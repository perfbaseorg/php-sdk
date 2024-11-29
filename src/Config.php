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

    /**
     * Creates a new configuration instance
     *
     * @param string $apiKey API key for authentication
     */
    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
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
        return 'https://receiver.perfbase.com/v1';
    }

}
