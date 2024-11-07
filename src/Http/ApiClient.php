<?php

namespace Perfbase\SDK\Http;

use Perfbase\SDK\Config;
use Perfbase\SDK\Exceptions\PerfbaseException;

class ApiClient
{
    private Config $config;
    private array $defaultHeaders;

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

    public function post(string $endpoint, array $data): array
    {
        $url = rtrim($this->config->getApiUrl(), '/') . '/' . ltrim($endpoint, '/');

        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => $this->formatHeaders($this->defaultHeaders),
            // Don't wait for response
            CURLOPT_TIMEOUT => 1,
            CURLOPT_NOSIGNAL => 1,
            // Don't return response
            CURLOPT_RETURNTRANSFER => false,
            // Close connection immediately
            CURLOPT_FORBID_REUSE => true,
            CURLOPT_FRESH_CONNECT => true,
        ]);

        $response = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        curl_close($ch);

        if ($error) {
            throw new PerfbaseException('HTTP Request failed: ' . $error);
        }

        if ($statusCode >= 400) {
            throw new PerfbaseException(
                'API request failed with status ' . $statusCode . ': ' . $response,
                $statusCode
            );
        }

        $decodedResponse = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new PerfbaseException('Failed to decode API response: ' . json_last_error_msg());
        }

        return $decodedResponse;
    }

    private function formatHeaders(array $headers): array
    {
        return array_map(
            fn($key, $value) => "$key: $value",
            array_keys($headers),
            $headers
        );
    }
}
