<?php

namespace Perfbase\SDK\Http;

use Perfbase\SDK\SubmitResult;
use RuntimeException;
use Throwable;

class CurlHttpClient implements HttpClientInterface
{
    private string $baseUrl;

    private int $timeout;

    private ?string $proxy;

    /**
     * @var callable(array<string, mixed>): array{status_code:int, body:string}|null
     */
    private $executor;

    /**
     * @param callable(array<string, mixed>): array{status_code:int, body:string}|null $executor
     */
    public function __construct(string $baseUrl, int $timeout, ?string $proxy = null, $executor = null)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->timeout = $timeout;
        $this->proxy = $proxy;
        $this->executor = $executor;
    }

    public function post(string $uri, array $options = []): SubmitResult
    {
        $request = $this->buildRequest($uri, $options);

        try {
            $response = $this->execute($request);
            $statusCode = isset($response['status_code']) && is_int($response['status_code'])
                ? $response['status_code']
                : null;
            $body = isset($response['body']) && is_string($response['body'])
                ? $response['body']
                : '';

            if ($statusCode !== null && $statusCode >= 200 && $statusCode < 300) {
                return SubmitResult::success($statusCode);
            }

            return $this->classifyHttpStatus($statusCode, $body);
        } catch (Throwable $e) {
            return SubmitResult::retryableFailure(null, $e->getMessage());
        }
    }

    /**
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    private function buildRequest(string $uri, array $options): array
    {
        $url = $this->baseUrl . '/' . ltrim($uri, '/');

        return [
            'url' => $url,
            'headers' => isset($options['headers']) && is_array($options['headers']) ? $options['headers'] : [],
            'body' => isset($options['body']) && is_string($options['body']) ? $options['body'] : '',
            'timeout' => $this->timeout,
            'proxy' => $this->proxy,
        ];
    }

    /**
     * @param array<string, mixed> $request
     * @return array{status_code:int, body:string}
     */
    private function execute(array $request): array
    {
        if ($this->executor !== null) {
            /** @var array{status_code:int, body:string} $response */
            $response = call_user_func($this->executor, $request);
            return $response;
        }

        return $this->performCurlRequest($request);
    }

    /**
     * @param array<string, mixed> $request
     * @return array{status_code:int, body:string}
     */
    private function performCurlRequest(array $request): array
    {
        $handle = curl_init();

        if ($handle === false) {
            throw new RuntimeException('Failed to initialize cURL');
        }

        try {
            curl_setopt_array($handle, $this->createCurlOptions($request));

            $responseBody = curl_exec($handle);
            if ($responseBody === false) {
                throw new RuntimeException(curl_error($handle));
            }

            $statusCode = curl_getinfo($handle, CURLINFO_RESPONSE_CODE);

            return [
                'status_code' => is_int($statusCode) ? $statusCode : 0,
                'body' => (string) $responseBody,
            ];
        } finally {
            curl_close($handle);
        }
    }

    /**
     * @param array<string, mixed> $request
     * @return array<int, mixed>
     */
    private function createCurlOptions(array $request): array
    {
        $headers = [];
        $requestHeaders = isset($request['headers']) && is_array($request['headers'])
            ? $request['headers']
            : [];

        foreach ($requestHeaders as $key => $value) {
            if (!is_string($key) || !is_string($value)) {
                continue;
            }

            $headers[] = sprintf('%s: %s', $key, $value);
        }

        $options = [
            CURLOPT_URL => $request['url'],
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $request['body'],
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_CONNECTTIMEOUT => $request['timeout'],
            CURLOPT_TIMEOUT => $request['timeout'],
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ];

        if (is_string($request['proxy']) && $request['proxy'] !== '') {
            $this->applyProxyOptions($options, $request['proxy']);
        }

        return $options;
    }

    /**
     * @param array<int, mixed> $options
     */
    private function applyProxyOptions(array &$options, string $proxy): void
    {
        $parts = parse_url($proxy);

        if (!is_array($parts) || !isset($parts['host'], $parts['scheme'])) {
            return;
        }

        $proxyHost = $parts['host'];
        if (isset($parts['port'])) {
            $proxyHost .= ':' . (string) $parts['port'];
        }

        $options[CURLOPT_PROXY] = $proxyHost;

        if (isset($parts['user'])) {
            $userInfo = $parts['user'];
            if (isset($parts['pass'])) {
                $userInfo .= ':' . $parts['pass'];
            }

            $options[CURLOPT_PROXYUSERPWD] = $userInfo;
        }

        $scheme = strtolower($parts['scheme']);
        if ($scheme === 'http') {
            $options[CURLOPT_PROXYTYPE] = CURLPROXY_HTTP;
            return;
        }

        if ($scheme === 'https') {
            if (defined('CURLPROXY_HTTPS')) {
                $options[CURLOPT_PROXYTYPE] = CURLPROXY_HTTPS;
            } else {
                $options[CURLOPT_PROXYTYPE] = CURLPROXY_HTTP;
            }

            return;
        }

        if ($scheme === 'socks5') {
            $options[CURLOPT_PROXYTYPE] = CURLPROXY_SOCKS5;
            return;
        }

        if ($scheme === 'socks5h') {
            $options[CURLOPT_PROXYTYPE] = defined('CURLPROXY_SOCKS5_HOSTNAME')
                ? CURLPROXY_SOCKS5_HOSTNAME
                : CURLPROXY_SOCKS5;
        }
    }

    /**
     * Classify an HTTP status code into a submit result.
     *
     * 429 and 5xx are retryable. 4xx (except 429) are permanent.
     */
    private function classifyHttpStatus(?int $statusCode, string $message = ''): SubmitResult
    {
        if ($statusCode === null || $statusCode === 0) {
            return SubmitResult::retryableFailure(null, $message);
        }

        if ($statusCode === 429 || $statusCode >= 500) {
            return SubmitResult::retryableFailure($statusCode, $message);
        }

        return SubmitResult::permanentFailure($statusCode, $message);
    }
}
