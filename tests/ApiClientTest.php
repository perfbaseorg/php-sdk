<?php

namespace Perfbase\SDK\Tests;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Response;
use JsonException;
use Perfbase\SDK\Config;
use Perfbase\SDK\Exception\PerfbaseApiKeyMissingException;
use Perfbase\SDK\Http\ApiClient;
use ReflectionClass;

/**
 * @coversDefaultClass \Perfbase\SDK\Http\ApiClient
 */
class ApiClientTest extends BaseTest
{

    /**
     * @return void
     * @throws PerfbaseApiKeyMissingException
     * @covers ::__construct
     */
    public function testThrowsExceptionIfApiKeyIsMissing(): void
    {
        $config = new Config(); // api_key is null by default

        $this->expectException(PerfbaseApiKeyMissingException::class);

        new ApiClient($config);
    }

    /**
     * @return void
     * @throws PerfbaseApiKeyMissingException
     * @covers ::__construct
     */
    public function testInitializesWithValidApiKey(): void
    {
        $config = new Config();
        $config->api_key = 'test_api_key';

        $apiClient = new ApiClient($config);

        $this->assertInstanceOf(ApiClient::class, $apiClient);
    }

}
