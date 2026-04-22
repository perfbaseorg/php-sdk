<?php

namespace Perfbase\SDK\Tests\Http;

use Mockery;
use Mockery\MockInterface;
use Perfbase\SDK\Config;
use Perfbase\SDK\Exception\PerfbaseInvalidConfigException;
use Perfbase\SDK\Http\ApiClient;
use Perfbase\SDK\Http\HttpClientInterface;
use Perfbase\SDK\SubmitResult;
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
        $this->assertEquals('keep-alive', $headers['Connection']);
        $this->assertStringContainsString('Perfbase-PHP-SDK/', $headers['User-Agent']);
    }

    /**
     * @covers ::submitTrace
     * @covers ::submit
     */
    public function testSubmitTraceReturnsResult(): void
    {
        $testData = 'test-trace-data';
        $expectedResult = SubmitResult::success(202);

        $this->mockHttpClient->shouldReceive('post')
            ->once()
            ->with('/v1/submit', Mockery::on(function ($options) use ($testData) {
                return isset($options['body']) && $options['body'] === $testData
                    && isset($options['headers']) && is_array($options['headers'])
                    && $options['headers']['X-Perfbase-Version'] === '0.1.0'
                    && $options['headers']['X-Perfbase-Protocol'] === '1';
            }))
            ->andReturn($expectedResult);

        $apiClient = new ApiClient($this->config, $this->mockHttpClient);
        $result = $apiClient->submitTrace($testData, '0.1.0', 1);

        $this->assertTrue($result->isSuccess());
        $this->assertSame(202, $result->getStatusCode());
    }

    /**
     * @covers ::submitTrace
     * @covers ::submit
     */
    public function testSubmitTracePassesCorrectHeaders(): void
    {
        $clientCreatedAt = '2026-04-08T12:00:00Z';

        $this->mockHttpClient->shouldReceive('post')
            ->once()
            ->with('/v1/submit', Mockery::on(function ($options) use ($clientCreatedAt) {
                $headers = $options['headers'];
                return $headers['Authorization'] === 'Bearer test-api-key'
                    && $headers['Accept'] === 'application/json'
                    && $headers['Content-Type'] === 'application/octet-stream'
                    && $headers['Connection'] === 'keep-alive'
                    && $headers['X-Perfbase-Version'] === '0.1.0'
                    && $headers['X-Perfbase-Protocol'] === '1'
                    && $headers['X-Perfbase-Client-Created-At'] === $clientCreatedAt
                    && isset($headers['User-Agent']);
            }))
            ->andReturn(SubmitResult::success());

        $apiClient = new ApiClient($this->config, $this->mockHttpClient);
        $result = $apiClient->submitTrace('test-data', '0.1.0', 1, $clientCreatedAt);

        $this->assertTrue($result->isSuccess());
    }

    /**
     * @covers ::submitTrace
     * @covers ::submit
     */
    public function testSubmitTraceWithoutClientCreatedAt(): void
    {
        $this->mockHttpClient->shouldReceive('post')
            ->once()
            ->with('/v1/submit', Mockery::on(function ($options) {
                $headers = $options['headers'];

                return $options['body'] === 'test-data'
                    && $headers['Content-Type'] === 'application/octet-stream'
                    && $headers['X-Perfbase-Version'] === '0.1.0'
                    && $headers['X-Perfbase-Protocol'] === '1'
                    && !isset($headers['X-Perfbase-Client-Created-At']);
            }))
            ->andReturn(SubmitResult::success());

        $apiClient = new ApiClient($this->config, $this->mockHttpClient);
        $result = $apiClient->submitTrace('test-data', '0.1.0', 1);

        $this->assertTrue($result->isSuccess());
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
                return $options['body'] === ''
                    && $options['headers']['Content-Type'] === 'application/octet-stream'
                    && $options['headers']['X-Perfbase-Version'] === '0.1.0'
                    && $options['headers']['X-Perfbase-Protocol'] === '1'
                    && !isset($options['headers']['X-Perfbase-Client-Created-At']);
            }))
            ->andReturn(SubmitResult::success());

        $apiClient = new ApiClient($this->config, $this->mockHttpClient);
        $result = $apiClient->submitTrace('', '0.1.0', 1);

        $this->assertTrue($result->isSuccess());
    }

    /**
     * @covers ::submitTrace
     */
    public function testSubmitTracePropagateshFailureResult(): void
    {
        $failureResult = SubmitResult::retryableFailure(503, 'Service Unavailable');

        $this->mockHttpClient->shouldReceive('post')
            ->once()
            ->andReturn($failureResult);

        $apiClient = new ApiClient($this->config, $this->mockHttpClient);
        $result = $apiClient->submitTrace('test-data', '0.1.0', 1);

        $this->assertTrue($result->isRetryable());
        $this->assertSame(503, $result->getStatusCode());
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
}
