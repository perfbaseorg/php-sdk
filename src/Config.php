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
    private ?string $api_key = null;

    /**
     * Base URL for the Perfbase API
     * @var string
     */
    private string $api_url = 'https://ingress.perfbase.cloud';

    /**
     * Proxy server to use for connecting to the Perfbase API
     * Format: [scheme]://[user]:[password]@[host]:[port]
     * Eg: http://username:password@proxy.example.com:8080
     * @var string|null
     */
    private ?string $proxy = null;

    /**
     * Timeout for API requests in seconds
     * Default: 10 seconds
     * @var int
     */
    private int $timeout = 10;

    /**
     * The features to utilise while profiling
     * @var int
     */
    private int $flags = FeatureFlags::DefaultFlags;

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
        self::assertValidApiKey($this->api_key);
        self::assertValidApiUrl($this->api_url);
        self::assertValidProxy($this->proxy);
        self::assertValidTimeout($this->timeout);
        self::assertValidFlags($this->flags);
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

            self::assertValidPropertyType($key, $value);
            $instance->$key = $value;
        }

        // Validate the configuration
        $instance->validate();

        return $instance;
    }

    public function getApiKey(): ?string
    {
        return $this->api_key;
    }

    public function getApiUrl(): string
    {
        return $this->api_url;
    }

    public function getProxy(): ?string
    {
        return $this->proxy;
    }

    public function getTimeout(): int
    {
        return $this->timeout;
    }

    public function getFlags(): int
    {
        return $this->flags;
    }

    /**
     * @throws PerfbaseInvalidConfigException
     */
    public function withFlags(int $flags): self
    {
        self::assertValidFlags($flags);

        $clone = clone $this;
        $clone->flags = $flags;

        return $clone;
    }

    /**
     * @param mixed $value
     * @throws PerfbaseInvalidConfigException
     */
    private static function assertValidPropertyType(string $key, $value): void
    {
        if ($key === 'api_key' || $key === 'proxy') {
            if (!is_string($value) && $value !== null) {
                throw new PerfbaseInvalidConfigException(sprintf('Configuration option "%s" must be a string', $key));
            }

            return;
        }

        if ($key === 'api_url') {
            if (!is_string($value)) {
                throw new PerfbaseInvalidConfigException('Configuration option "api_url" must be a string');
            }

            return;
        }

        if ($key === 'timeout' || $key === 'flags') {
            if (!is_int($value)) {
                throw new PerfbaseInvalidConfigException(sprintf('Configuration option "%s" must be an integer', $key));
            }
        }
    }

    /**
     * @throws PerfbaseInvalidConfigException
     */
    private static function assertValidApiKey(?string $apiKey): void
    {
        if ($apiKey === null || trim($apiKey) === '') {
            throw new PerfbaseInvalidConfigException('API key is required');
        }
    }

    /**
     * @throws PerfbaseInvalidConfigException
     */
    private static function assertValidApiUrl(string $apiUrl): void
    {
        if (trim($apiUrl) === '') {
            throw new PerfbaseInvalidConfigException('API URL is required');
        }

        if (!filter_var($apiUrl, FILTER_VALIDATE_URL)) {
            throw new PerfbaseInvalidConfigException('API URL is not valid');
        }

        $scheme = parse_url($apiUrl, PHP_URL_SCHEME);
        if (!is_string($scheme) || strtolower($scheme) !== 'https') {
            throw new PerfbaseInvalidConfigException('API URL must use HTTPS');
        }
    }

    /**
     * @throws PerfbaseInvalidConfigException
     */
    private static function assertValidProxy(?string $proxy): void
    {
        if ($proxy === null) {
            return;
        }

        if (!filter_var($proxy, FILTER_VALIDATE_URL)) {
            throw new PerfbaseInvalidConfigException('Proxy URL is not valid');
        }

        $scheme = parse_url($proxy, PHP_URL_SCHEME);
        $allowedSchemes = ['http', 'https', 'socks5', 'socks5h'];

        if (!is_string($scheme) || !in_array(strtolower($scheme), $allowedSchemes, true)) {
            throw new PerfbaseInvalidConfigException('Proxy URL must use http, https, socks5, or socks5h');
        }
    }

    /**
     * @throws PerfbaseInvalidConfigException
     */
    private static function assertValidTimeout(int $timeout): void
    {
        if ($timeout <= 0) {
            throw new PerfbaseInvalidConfigException('Timeout must be a positive integer');
        }
    }

    /**
     * @throws PerfbaseInvalidConfigException
     */
    private static function assertValidFlags(int $flags): void
    {
        if ($flags < 0 || $flags > FeatureFlags::AllFlags) {
            throw new PerfbaseInvalidConfigException('Invalid flags value');
        }
    }
}
