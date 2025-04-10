<?php

namespace Perfbase\SDK;

use Perfbase\SDK\Exception\PerfbaseInvalidConfigException;

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
    /**
     * The API key to use for authenticating with the Perfbase API
     * @var string|null
     */
    public ?string $api_key = null;

    /**
     * Base URL for the Perfbase API
     * @var string
     */
    public string $api_url = 'https://receiver.perfbase.com';

    /**
     * Proxy server to use for connecting to the Perfbase API
     * Format: [scheme]://[user]:[password]@[host]:[port]
     * Eg: http://username:password@proxy.example.com:8080
     * @var string|null
     */
    public ?string $proxy = null;

    /**
     * Timeout for API requests in seconds
     * Default: 10 seconds
     * @var int
     */
    public int $timeout = 10;

    /**
     * The features to utilise while profiling
     * @var int
     */
    public int $flags = FeatureFlags::DefaultFlags;

    /**
     * @param string|null $api_key
     * @param int|null $flags
     * @param string|null $api_url
     * @param string|null $proxy
     * @param int|null $timeout
     */
    private function __construct(
        ?string $api_key = null,
        ?int    $flags = FeatureFlags::DefaultFlags,
        ?string $api_url = null,
        ?string $proxy = null,
        ?int    $timeout = 10
    )
    {
        $this->api_key = $api_key;
        $this->flags = $flags ?? $this->flags;
        $this->api_url = $api_url ?? $this->api_url;
        $this->proxy = $proxy;
        $this->timeout = $timeout ?? $this->timeout;
    }

    /**
     * Create a new Config instance
     *
     * @param string|null $api_key
     * @param int|null $flags
     * @param string|null $api_url
     * @param string|null $proxy
     * @param int|null $timeout
     * @return Config
     * @throws PerfbaseInvalidConfigException
     */
    public static function new(
        ?string $api_key = null,
        ?int    $flags = FeatureFlags::DefaultFlags,
        ?string $api_url = null,
        ?string $proxy = null,
        ?int    $timeout = 10
    ): Config
    {
        $config = new self(
            $api_key,
            $flags,
            $api_url,
            $proxy,
            $timeout
        );

        // Validate the configuration
        $config->validate();
        return $config;
    }

    public function validate(): void
    {
        // Check if API key is missing
        if (empty($this->api_key)) {
            throw new PerfbaseInvalidConfigException('API key is required');
        }

        // Check if API key is a valid string
        if (empty($this->api_url)) {
            throw new PerfbaseInvalidConfigException('API URL is required');
        }

        // Check if API url is a valid URL
        if (!filter_var($this->api_url, FILTER_VALIDATE_URL)) {
            throw new PerfbaseInvalidConfigException('API URL is not valid');
        }

        // Check if proxy is a valid URL
        if ($this->timeout <= 0) {
            throw new PerfbaseInvalidConfigException('Timeout must be a positive integer');
        }

        // Check if flags are invalid
        if ($this->flags < 0 || $this->flags > FeatureFlags::AllFlags) {
            throw new PerfbaseInvalidConfigException('Invalid flags value');
        }
    }

    /**
     * Create a new Config instance from an array of configuration options
     * @param array<string, mixed> $config
     * @return self
     * @throws PerfbaseInvalidConfigException
     */
    public static function fromArray(array $config): self
    {
        $instance = new self();

        foreach ($config as $key => $value) {
            if (!property_exists($instance, $key)) {
                throw new PerfbaseInvalidConfigException(sprintf('Invalid configuration option: %s', $key));
            }
            $instance->$key = $value;
        }

        // Validate the configuration
        $instance->validate();

        return $instance;
    }
}