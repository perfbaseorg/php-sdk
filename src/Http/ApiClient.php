<?php

namespace Perfbase\SDK\Http;

use GuzzleHttp\Client as GuzzleClient;
use Perfbase\SDK\Config;
use Perfbase\SDK\Perfbase;
use Perfbase\SDK\SubmitResult;

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
     * @var HttpClientInterface
     */
    private HttpClientInterface $httpClient;

    public function __construct(Config $config, ?HttpClientInterface $httpClient = null)
    {
        $this->config = $config;
        $this->defaultHeaders = [
            'Authorization' => 'Bearer ' . $this->config->api_key,
            'Accept' => 'application/json',
            'User-Agent' => sprintf('Perfbase-PHP-SDK/%s', Perfbase::VERSION),
            'Connection' => 'keep-alive',
        ];

        if ($httpClient !== null) {
            $this->httpClient = $httpClient;
        } else {
            /** @var array<string, mixed> $httpClientConfig */
            $httpClientConfig = [];
            $httpClientConfig['base_uri'] = $config->api_url;
            $httpClientConfig['timeout'] = $config->timeout;

            if ($config->proxy) {
                $httpClientConfig['proxy'] = $config->proxy;
            }

            $guzzleClient = new GuzzleClient($httpClientConfig);
            $this->httpClient = new GuzzleHttpClient($guzzleClient);
        }
    }

    /**
     * Submits a trace to the Perfbase API
     *
     * @param string $perfData Data to send in the request body
     * @param int $payloadVersion Payload encoding version. Defaults to `1` for backwards compatibility.
     * @param string|null $clientCreatedAt Trace creation timestamp in ISO 8601 UTC
     * @return SubmitResult
     */
    public function submitTrace(string $perfData, int $payloadVersion = 1, ?string $clientCreatedAt = null): SubmitResult
    {
        return $this->submit('/v1/submit', $perfData, $payloadVersion, $clientCreatedAt);
    }

    /**
     * Sends a POST request to the specified API endpoint
     *
     * @param string $endpoint API endpoint to send the request to
     * @param string $perfData Data to send in the request body
     * @param int $payloadVersion Payload encoding version
     * @param string|null $clientCreatedAt Trace creation timestamp in ISO 8601 UTC
     * @return SubmitResult
     */
    private function submit(string $endpoint, string $perfData, int $payloadVersion, ?string $clientCreatedAt = null): SubmitResult
    {
        $headers = $this->defaultHeaders;
        $headers['Content-Type'] = 'application/octet-stream';
        $headers['X-Perfbase-Payload-Version'] = (string) $payloadVersion;

        if ($clientCreatedAt !== null) {
            $headers['X-Perfbase-Client-Created-At'] = $clientCreatedAt;
        }

        $options = [
            'headers' => $headers,
            'body' => $perfData,
        ];

        return $this->httpClient->post($endpoint, $options);
    }
}
