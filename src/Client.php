<?php

namespace Perfbase\SDK;

use Perfbase\SDK\Http\ApiClient;
use Perfbase\SDK\Config;

class Client
{
    private Config $config;
    private ApiClient $apiClient;
    private array $profileData = [];

    public function __construct(Config $config)
    {
        $this->config = $config;
        $this->apiClient = new ApiClient($config);
    }

    public function startProfiling(): void
    {
        if (function_exists('perfbase_enable')) {
            perfbase_enable();
        }
    }

    public function stopProfiling(): void
    {
        if (function_exists('perfbase_disable')) {
            $this->profileData = perfbase_disable();
            $this->sendProfilingData();
        }
    }

    private function sendProfilingData(): void
    {
        $this->apiClient->post('/profiles', [
            'profile_data' => $this->profileData
        ]);
    }
}
