<?php

namespace Tests;

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
class ApiClientTest extends \PHPUnit\Framework\TestCase
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

    /**
     * @return void
     * @throws PerfbaseApiKeyMissingException
     * @throws JsonException
     * @covers ::get
     */
    public function testSendsSynchronousPostRequestAndReturnsResponse(): void
    {
        $mock = new MockHandler([
            new Response(200, [], 'Success'),
        ]);
        $handlerStack = HandlerStack::create($mock);
        $guzzleClient = new GuzzleClient(['handler' => $handlerStack]);

        $config = new Config();
        $config->api_key = 'test_api_key';

        $apiClient = new ApiClient($config);
        $reflection = new ReflectionClass($apiClient);
        $property = $reflection->getProperty('httpClient');
        $property->setAccessible(true);
        $property->setValue($apiClient, $guzzleClient);

        $response = $apiClient->post('/test-endpoint', ['key' => 'value'], false);

        $this->assertSame('Success', $response);
    }

    /**
     * @return void
     * @throws JsonException
     * @throws PerfbaseApiKeyMissingException
     * @covers ::post
     */
    public function testSendsAsynchronousPostRequestAndDoesNotBlock(): void
    {
        $mock = new MockHandler([
            new Response(200, [], 'Success'),
        ]);
        $handlerStack = HandlerStack::create($mock);
        $guzzleClient = new GuzzleClient(['handler' => $handlerStack]);

        $config = new Config();
        $config->api_key = 'test_api_key';

        $apiClient = new ApiClient($config);
        $reflection = new ReflectionClass($apiClient);
        $property = $reflection->getProperty('httpClient');
        $property->setAccessible(true);
        $property->setValue($apiClient, $guzzleClient);

        $response = $apiClient->post('/test-endpoint', ['key' => 'value'], true);

        $this->assertNull($response);

        // Check if promises array is populated
        $promisesProperty = $reflection->getProperty('promises');
        $promisesProperty->setAccessible(true);

        /** @var array<PromiseInterface> $promises */
        $promises = $promisesProperty->getValue($apiClient);
        $this->assertIsArray($promises);
        $this->assertCount(1, $promises);
        $this->assertInstanceOf(PromiseInterface::class, $promises[0]);
    }
}
