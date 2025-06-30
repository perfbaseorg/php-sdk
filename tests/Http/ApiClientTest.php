<?php

namespace Perfbase\SDK\Tests\Http;

use Mockery;
use Mockery\MockInterface;
use Perfbase\SDK\Config;
use Perfbase\SDK\Exception\PerfbaseInvalidConfigException;
use Perfbase\SDK\Http\ApiClient;
use Perfbase\SDK\Http\HttpClientInterface;
use Perfbase\SDK\Tests\BaseTest;

/**
 * @coversDefaultClass \Perfbase\SDK\Http\ApiClient
 */
class ApiClientTest extends BaseTest
{
    private MockInterface $mockHttpClient;
    private Config $config;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->mockHttpClient = Mockery::mock(HttpClientInterface::class);
        $this->config = Config::fromArray([
            'api_key' => 'test-api-key',
            'api_url' => 'https://test.example.com'
        ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * @covers ::__construct
     * @throws PerfbaseInvalidConfigException
     */
    public function testConstructorWithMockedHttpClient(): void
    {
        $apiClient = new ApiClient($this->config, $this->mockHttpClient);
        
        $this->assertInstanceOf(ApiClient::class, $apiClient);
    }

    /**
     * @covers ::__construct
     * @throws PerfbaseInvalidConfigException
     */
    public function testConstructorWithoutHttpClientCreatesDefault(): void
    {
        $apiClient = new ApiClient($this->config);
        
        $this->assertInstanceOf(ApiClient::class, $apiClient);
    }

    /**
     * @covers ::__construct
     */
    public function testConstructorSetsCorrectHeaders(): void
    {
        $apiClient = new ApiClient($this->config, $this->mockHttpClient);
        
        $headers = $this->getPrivateFieldValue($apiClient, 'defaultHeaders');
        
        $this->assertEquals('Bearer test-api-key', $headers['Authorization']);
        $this->assertEquals('application/json', $headers['Accept']);
        $this->assertEquals('application/json', $headers['Content-Type']);
        $this->assertEquals('keep-alive', $headers['Connection']);
        $this->assertStringContainsString('Perfbase-PHP-SDK/', $headers['User-Agent']);
    }

    /**
     * @covers ::submitTrace
     * @covers ::submit
     */
    public function testSubmitTrace(): void
    {
        $testData = 'test-trace-data';
        
        $this->mockHttpClient->shouldReceive('post')
            ->once()
            ->with('/v1/submit', Mockery::on(function ($options) use ($testData) {
                return isset($options['body']) && $options['body'] === $testData
                    && isset($options['headers']) && is_array($options['headers']);
            }));
        
        $apiClient = new ApiClient($this->config, $this->mockHttpClient);
        
        $apiClient->submitTrace($testData);
        
        $this->assertTrue(true); // Verify no exception was thrown
    }

    /**
     * @covers ::submitTrace
     * @covers ::submit
     */
    public function testSubmitTraceWithCorrectHeaders(): void
    {
        $testData = 'test-trace-data';
        
        $this->mockHttpClient->shouldReceive('post')
            ->once()
            ->with('/v1/submit', Mockery::on(function ($options) {
                $headers = $options['headers'];
                return $headers['Authorization'] === 'Bearer test-api-key'
                    && $headers['Accept'] === 'application/json'
                    && $headers['Content-Type'] === 'application/json'
                    && $headers['Connection'] === 'keep-alive'
                    && isset($headers['User-Agent']);
            }));
        
        $apiClient = new ApiClient($this->config, $this->mockHttpClient);
        
        $apiClient->submitTrace($testData);
        
        $this->assertTrue(true); // Verify no exception was thrown
    }

    /**
     * @covers ::submitTrace
     * @covers ::submit
     */
    public function testSubmitTraceWithEmptyData(): void
    {
        $this->mockHttpClient->shouldReceive('post')
            ->once()
            ->with('/v1/submit', Mockery::on(function ($options) {
                return $options['body'] === '';
            }));
        
        $apiClient = new ApiClient($this->config, $this->mockHttpClient);
        
        $apiClient->submitTrace('');
        
        $this->assertTrue(true); // Verify no exception was thrown
    }

    /**
     * @covers ::__construct
     */
    public function testConstructorWithProxyConfiguration(): void
    {
        $configWithProxy = Config::fromArray([
            'api_key' => 'test-api-key',
            'api_url' => 'https://test.example.com',
            'proxy' => 'http://proxy.example.com:8080'
        ]);
        
        // When not providing a mock HTTP client, it should create a real one with proxy config
        $apiClient = new ApiClient($configWithProxy);
        
        $this->assertInstanceOf(ApiClient::class, $apiClient);
    }

    /**
     * @covers ::__construct
     */
    public function testConstructorWithCustomTimeout(): void
    {
        $configWithTimeout = Config::fromArray([
            'api_key' => 'test-api-key',
            'api_url' => 'https://test.example.com',
            'timeout' => 30
        ]);
        
        $apiClient = new ApiClient($configWithTimeout);
        
        $this->assertInstanceOf(ApiClient::class, $apiClient);
    }

    /**
     * Test that HTTP client exceptions are handled gracefully
     * @covers ::submit
     */
    public function testSubmitHandlesHttpClientExceptions(): void
    {
        $this->mockHttpClient->shouldReceive('post')
            ->once()
            ->andThrow(new \Exception('HTTP error'));
        
        $apiClient = new ApiClient($this->config, $this->mockHttpClient);
        
        // Should throw exception since we removed silent failure handling
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('HTTP error');
        
        $apiClient->submitTrace('test-data');
    }
}