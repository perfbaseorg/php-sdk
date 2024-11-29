<?php

namespace Perfbase\SDK\Http;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Promise\Utils;
use Perfbase\SDK\Config;
use Perfbase\SDK\Exception\PerfbaseException;

class ApiClient
{
    private Config $config;
    private array $defaultHeaders;
    private Client $httpClient;
    private array $promises = [];

    public function __construct(Config $config)
    {
        $this->config = $config;
        $this->defaultHeaders = [
            'Authorization' => 'Bearer ' . $this->config->getApiKey(),
            'Accept' => 'application/json',
            'User-Agent' => 'Perfbase-PHP-SDK/1.0',
            'Content-Type' => 'application/json',
        ];
        $this->httpClient = new Client([
            'base_uri' => rtrim($this->config->getApiUrl(), '/') . '/',
            'timeout' => 10.0, // Adjust timeout as needed
        ]);
    }

    /**
     * Sends a POST request to the specified API endpoint
     *
     * @param string $endpoint API endpoint to send the request to
     * @param array $data Data to send in the request body
     * @param bool $async If true, send asynchronously; if false, wait for response
     *
     * @return array|null Response data from the API, or null if non-blocking
     * @throws PerfbaseException|GuzzleException When the HTTP request fails or returns an error
     */
    public function post(string $endpoint, array $data, bool $async = true): ?array
    {
        // Convert data to JSON and compress
        $jsonData = json_encode($data);
        if ($jsonData === false) {
            throw new PerfbaseException('Failed to encode data as JSON: ' . json_last_error_msg());
        }

        $compressedData = gzencode($jsonData);
        if ($compressedData === false) {
            throw new PerfbaseException('Failed to gzip compress the request data.');
        }

        // Prepare request options
        $options = [
            'headers' => array_merge($this->defaultHeaders, ['Content-Encoding' => 'gzip']),
            'body' => $compressedData,
        ];

        if ($async) {
            // Send an asynchronous (non-blocking) request
            $promise = $this->httpClient->postAsync($endpoint, $options);

            // Optionally handle the promise's fulfillment or rejection
            $promise->then(
                function ($response) {
                    // Success callback (optional)
                },
                function ($exception) {
                    // Error callback (optional)
                }
            );

            // Store the promise to settle later
            $this->promises[] = $promise;

            // Return null since we won't have a response immediately
            return null;
        } else {
            // Send a synchronous (blocking) request
            try {
                $response = $this->httpClient->post($endpoint, $options);
                $body = (string)$response->getBody();
                $decodedResponse = json_decode($body, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new PerfbaseException('Failed to decode API response: ' . json_last_error_msg());
                }

                return $decodedResponse;
            } catch (\Exception $e) {
                throw new PerfbaseException('HTTP Request failed: ' . $e->getMessage());
            }
        }
    }

    public function __destruct()
    {
        // Attempt to settle all outstanding promises without blocking
        if (!empty($this->promises)) {
            Utils::settle($this->promises)->wait(false);
        }
    }
}