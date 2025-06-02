<?php

namespace Perfbase\SDK\Http;

use GuzzleHttp\Client as GuzzleClient;
use Perfbase\SDK\Config;
use Perfbase\SDK\Perfbase;
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

    public function __construct(Config $config)
    {
        $this->config = $config;
        $this->defaultHeaders = [
            'Authorization' => 'Bearer ' . $this->config->api_key,
            'Accept' => 'application/json',
            'User-Agent' => sprintf('Perfbase-PHP-SDK/%s', Perfbase::VERSION),
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
     * Submits a trace to the Perfbase API
     *
     * @param string $perfData Data to send in the request body
     * @return void
     */
    public function submitTrace(string $perfData): void
    {
        $this->submit('/v1/submit', $perfData);
    }

    /**
     * Sends a POST request to the specified API endpoint
     *
     * @param string $endpoint API endpoint to send the request to
     * @param string $perfData Data to send in the request body
     * @return void
     */
    private function submit(string $endpoint, string $perfData): void
    {
        // Prepare request options
        $options = [
            'headers' => array_merge($this->defaultHeaders, []),
            'body' => $perfData,
        ];

        try {
            $this->httpClient->post($endpoint, $options);
        } catch (Throwable $e) {
            // throw new PerfbaseException('HTTP Request failed: ' . $e->getMessage());
        }
    }

}
