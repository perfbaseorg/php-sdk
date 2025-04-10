<?php

namespace Perfbase\SDK\Tests;

use Perfbase\SDK\Config;
use Perfbase\SDK\Exception\PerfbaseInvalidConfigException;
use Perfbase\SDK\Http\ApiClient;

/**
 * @coversDefaultClass \Perfbase\SDK\Http\ApiClient
 */
class ApiClientTest extends BaseTest
{

    /**
     * @return void
     * @covers ::__construct
     * @throws PerfbaseInvalidConfigException
     */
    public function testInitializesWithValidApiKey(): void
    {
        $config = Config::new('test_api_key', 0, 'http://example.com');
        $apiClient = new ApiClient($config);
        $this->assertInstanceOf(ApiClient::class, $apiClient);
    }

    /**
     * @return void
     * @covers ::__construct
     * @throws PerfbaseInvalidConfigException
     */
    public function testInitializesWithValidNullUrl(): void
    {
        $config = Config::new('test_api_key', 0, null);
        $apiClient = new ApiClient($config);
        $this->assertInstanceOf(ApiClient::class, $apiClient);
    }

}
