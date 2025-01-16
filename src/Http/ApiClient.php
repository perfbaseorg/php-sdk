<?php

namespace Perfbase\SDK\Http;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Promise\Utils;
use JsonException;
use Perfbase\SDK\Config;
use Perfbase\SDK\Exception\PerfbaseApiKeyMissingException;
use Throwable;

class ApiClient
{
    /**
     * Configuration object for the SDK
     * @var Config
     */
    private Config $config;

    /**
     * Default headers to send with each request
     * @var array<string, string>
     */
    private array $defaultHeaders;

    /**
     * HTTP client to send requests.
     * @var GuzzleClient
     */
    private GuzzleClient $httpClient;

    /**
     * Promises to settle before the client is destroyed
     * @var array<PromiseInterface>
     */
    private array $promises = [];

    /**
     * @throws PerfbaseApiKeyMissingException
     */
    public function __construct(Config $config)
    {
        if (!is_string($config->api_key)) {
            throw new PerfbaseApiKeyMissingException();
        }

        $this->config = $config;

        $this->defaultHeaders = [
            'Authorization' => 'Bearer ' . $this->config->api_key,
            'Accept' => 'application/json',
            'User-Agent' => 'Perfbase-PHP-SDK/1.0',
            'Content-Type' => 'application/json',
        ];

        /** @var array<string, mixed> $httpClientConfig */
        $httpClientConfig = [];

        $httpClientConfig['base_uri'] = $config->api_url;
        $httpClientConfig['timeout'] = $config->timeout;

        if ($config->proxy) {
            $httpClientConfig['proxy'] = $config->proxy;
        }

        $this->httpClient = new GuzzleClient($httpClientConfig);
    }

    /**
     * Sends a POST request to the specified API endpoint
     *
     * @param string $endpoint API endpoint to send the request to
     * @param array<mixed> $data Data to send in the request body
     * @param bool $async If true, send asynchronously; if false, wait for response
     *
     * @return string|null Response data from the API, or null if non-blocking
     * @throws JsonException When the HTTP request fails or returns an error
     */
    public function post(string $endpoint, array $data, bool $async = false): ?string
    {
        // Prepare request options
        $options = [
            'headers' => array_merge($this->defaultHeaders, []),
            'body' => json_encode($data, JSON_THROW_ON_ERROR),
        ];

        try {
            if ($async) {
                $this->promises[] = $this->httpClient->postAsync($endpoint, $options);
                return null;
            } else {
                $response = $this->httpClient->post($endpoint, $options);
                return (string)$response->getBody();
            }
        } catch (Throwable $e) {
            // throw new PerfbaseException('HTTP Request failed: ' . $e->getMessage());
        }
        return null;
    }

    public function __destruct()
    {
        // Attempt to settle all outstanding async HTTP promises without blocking
        if (!empty($this->promises)) {
            Utils::settle($this->promises)->wait(false);
        }
    }
}