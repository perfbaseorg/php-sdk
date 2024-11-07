<?php

namespace Perfbase\SDK;

/**
 * Configuration class for the Perfbase SDK
 * 
 * This class manages all configuration settings required for the SDK to function,
 * including API credentials, endpoints, and operational parameters.
 *
 * @package Perfbase\SDK
 */
class Config
{
    /** @var string API key for authentication */
    private string $apiKey;

    /** @var string Base URL for the Perfbase API */
    private string $apiUrl;

    /** @var bool Whether the SDK is enabled */
    private bool $enabled;

    /** @var int Timeout for API requests in seconds */
    private int $timeout;

    /**
     * Creates a new configuration instance
     *
     * @param string $apiKey   API key for authentication
     * @param string $apiUrl   Base URL for the Perfbase API
     * @param bool   $enabled  Whether the SDK is enabled
     * @param int    $timeout  Timeout for API requests in seconds
     */
    public function __construct(
        string $apiKey,
        string $apiUrl = 'https://api.perfbase.com/v1',
        bool $enabled = true,
        int $timeout = 1
    ) {
        $this->apiKey = $apiKey;
        $this->apiUrl = $apiUrl;
        $this->enabled = $enabled;
        $this->timeout = $timeout;
    }

    /**
     * Returns the configured API key
     *
     * @return string
     */
    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    /**
     * Returns the configured API URL
     *
     * @return string
     */
    public function getApiUrl(): string
    {
        return $this->apiUrl;
    }

    /**
     * Checks if the SDK is enabled
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Returns the configured timeout value
     *
     * @return int
     */
    public function getTimeout(): int
    {
        return $this->timeout;
    }

    /**
     * Creates a Config instance from an array of settings
     *
     * @param array{
     *     api_key: string,
     *     api_url?: string,
     *     enabled?: bool,
     *     timeout?: int
     * } $config Configuration array
     * 
     * @return self
     */
    public static function fromArray(array $config): self
    {
        return new self(
            $config['api_key'],
            $config['api_url'] ?? 'https://api.perfbase.com/v1',
            $config['enabled'] ?? true,
            $config['timeout'] ?? 1
        );
    }
}
