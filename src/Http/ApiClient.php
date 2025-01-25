<?php

namespace Perfbase\SDK\Http;

use GuzzleHttp\Client as GuzzleClient;
use JsonException;
use Perfbase\SDK\Config;
use Perfbase\SDK\Exception\PerfbaseInvalidConfigException;
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
     * @throws PerfbaseInvalidConfigException
     */
    public function __construct(Config $config)
    {
        if (!is_string($config->api_key)) {
            throw new PerfbaseInvalidConfigException();
        }

        $this->config = $config;
        $this->defaultHeaders = [
            'Authorization' => 'Bearer ' . $this->config->api_key,
            'Accept' => 'application/json',
            'User-Agent' => 'Perfbase-PHP-SDK/1.0',
            'Content-Type' => 'application/json',
            'Connection' => 'keep-alive',
        ];

        /** @var array<string, mixed> $httpClientConfig */
        $httpClientConfig = [];
        $httpClientConfig['base_uri'] = $config->api_url;
        $httpClientConfig['timeout'] = $config->timeout;

        // Set up proxy if configured
        if ($config->proxy) {
            $httpClientConfig['proxy'] = $config->proxy;
        }

        // Set up the HTTP client
        $this->httpClient = new GuzzleClient($httpClientConfig);
    }

    /**
     * Sends a POST request to the specified API endpoint
     *
     * @param string $endpoint API endpoint to send the request to
     * @param array<mixed> $data Data to send in the request body
     * @return void
     * @throws JsonException
     */
    private function submit(string $endpoint, array $data): void
    {
        // Prepare request options
        $options = [
            'headers' => array_merge($this->defaultHeaders, []),
            'body' => json_encode($data, JSON_THROW_ON_ERROR),
        ];

        try {
            $this->httpClient->post($endpoint, $options);
        } catch (Throwable $e) {
            // throw new PerfbaseException('HTTP Request failed: ' . $e->getMessage());
        }
    }

    /**
     * Submits a trace to the Perfbase API
     *
     * @param array<mixed> $data Data to send in the request body
     * @return void
     *
     * @throws JsonException When the HTTP request fails or returns an error
     */
    public function submitTrace(array $data): void
    {
        $this->submit('/v1/submit', $data);
    }

}