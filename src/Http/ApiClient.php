<?php

namespace Perfbase\SDK\Http;

use Perfbase\SDK\Config;
use Perfbase\SDK\Exceptions\PerfbaseException;

/**
 * HTTP Client for making requests to the Perfbase API
 * 
 * This class handles all HTTP communication with the Perfbase API,
 * including authentication and data formatting.
 *
 * @package Perfbase\SDK\Http
 */
class ApiClient
{
    /** @var Config SDK configuration instance */
    private Config $config;

    /** @var array<string, string> Default HTTP headers for all requests */
    private array $defaultHeaders;

    /**
     * Creates a new API client instance
     *
     * @param Config $config SDK configuration instance
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
        $this->defaultHeaders = [
            'Authorization' => 'Bearer ' . $this->config->getApiKey(),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'User-Agent' => 'Perfbase-PHP-SDK/1.0',
        ];
    }

    /**
     * Sends a POST request to the specified API endpoint
     *
     * @param string $endpoint API endpoint to send the request to
     * @param array  $data     Data to send in the request body
     * @param bool   $noWait   If true, don't wait for response and return empty array
     * 
     * @throws PerfbaseException When the HTTP request fails or returns an error
     * @return array Response data from the API
     */
    public function post(string $endpoint, array $data, bool $noWait = false): array
    {
        $url = rtrim($this->config->getApiUrl(), '/') . '/' . ltrim($endpoint, '/');

        $ch = curl_init($url);

        $curlOptions = [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => $this->formatHeaders($this->defaultHeaders),
        ];

        if ($noWait) {
            $curlOptions += [
                // Don't wait for response
                CURLOPT_TIMEOUT => 1,
                CURLOPT_NOSIGNAL => 1,
                // Don't return response
                CURLOPT_RETURNTRANSFER => false,
            ];
        } else {
            $curlOptions += [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => $this->config->getTimeout(),
            ];
        }

        curl_setopt_array($ch, $curlOptions);

        $response = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        curl_close($ch);

        if ($error) {
            throw new PerfbaseException(sprintf('HTTP Request failed: %s', $error));
        }

        if ($noWait) {
            return [];
        }

        if ($statusCode >= 400) {
            throw new PerfbaseException(
                sprintf('API request failed with status %d: %s', $statusCode, $response)
            );
        }

        $decodedResponse = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new PerfbaseException(
                sprintf('Failed to decode API response: %s', json_last_error_msg())
            );
        }

        return $decodedResponse;
    }

    /**
     * Formats HTTP headers for cURL
     *
     * @param array<string, string> $headers Associative array of headers
     * @return array<int, string> Formatted headers array
     */
    private function formatHeaders(array $headers): array
    {
        return array_map(
            fn($key, $value) => "$key: $value",
            array_keys($headers),
            $headers
        );
    }
}
