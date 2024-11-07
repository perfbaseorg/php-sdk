<?php

namespace Perfbase\SDK;

class Config
{
    private string $apiKey;
    private string $apiUrl;
    private bool $enabled;
    private int $timeout;

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

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    public function getApiUrl(): string
    {
        return $this->apiUrl;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function getTimeout(): int
    {
        return $this->timeout;
    }

    /**
     * Create a Config instance from an array of settings
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
