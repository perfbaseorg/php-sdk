<?php

namespace Perfbase\SDK;

use Perfbase\SDK\Http\ApiClient;

/**
 * Main client class for the Perfbase SDK
 * 
 * This class provides the primary interface for interacting with the Perfbase
 * profiling system. It handles starting and stopping profiling sessions and
 * sending the collected data to the Perfbase API.
 *
 * @package Perfbase\SDK
 */
class Client
{
    /** @var ApiClient HTTP client for making API requests */
    private ApiClient $apiClient;

    /** @var array Collected profiling data */
    private array $profileData = [];

    /**
     * Creates a new Perfbase client instance
     *
     * @param Config $config Configuration object containing API credentials and settings
     */
    public function __construct(Config $config)
    {
        $this->apiClient = new ApiClient($config);
    }

    /**
     * Starts the profiling session
     * 
     * Enables the Perfbase profiler if the extension is installed.
     * This method should be called at the point where you want to begin
     * collecting performance data.
     *
     * @return void
     */
    public function startProfiling(): void
    {
        if (function_exists('perfbase_enable')) {
            perfbase_enable();
        }
    }

    /**
     * Stops the profiling session and sends collected data
     * 
     * Disables the Perfbase profiler and retrieves the collected performance data.
     * The data is automatically sent to the Perfbase API for analysis.
     *
     * @return void
     */
    public function stopProfiling(): void
    {
        if (function_exists('perfbase_disable')) {
            $this->profileData = perfbase_disable();
            $this->sendProfilingData();
        }
    }

    /**
     * Sends collected profiling data to the API
     *
     * @return void
     */
    private function sendProfilingData(): void
    {
        $this->apiClient->post('/profiles', [
            'profile_data' => $this->profileData
        ]);
    }

    /**
     * Sends multiple profiling data entries in bulk to the API
     *
     * @param array $profileDataArray Array of profiling data entries
     * @return void
     */
    private function sendProfilingDataBulk(array $profileDataArray): void
    {
        $this->apiClient->post('/profiles/bulk', [
            'profile_data' => $profileDataArray
        ]);
    }
}
