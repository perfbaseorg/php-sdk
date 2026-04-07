<?php

namespace Perfbase\SDK\Tests\Http;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Mockery;
use Mockery\MockInterface;
use Perfbase\SDK\Http\GuzzleHttpClient;
use Perfbase\SDK\SubmitResult;
use Perfbase\SDK\Tests\BaseTest;

/**
 * @coversDefaultClass \Perfbase\SDK\Http\GuzzleHttpClient
 */
class GuzzleHttpClientTest extends BaseTest
{
    private MockInterface $mockGuzzleClient;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockGuzzleClient = Mockery::mock(GuzzleClient::class);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * @covers ::__construct
     */
    public function testConstructor(): void
    {
        $httpClient = new GuzzleHttpClient($this->mockGuzzleClient);
        $this->assertInstanceOf(GuzzleHttpClient::class, $httpClient);
    }

    /**
     * @covers ::post
     */
    public function testPostReturnsSuccessOn2xx(): void
    {
        $this->mockGuzzleClient->shouldReceive('post')
            ->once()
            ->andReturn(new Response(202));

        $httpClient = new GuzzleHttpClient($this->mockGuzzleClient);
        $result = $httpClient->post('/test/endpoint', ['body' => 'data']);

        $this->assertInstanceOf(SubmitResult::class, $result);
        $this->assertTrue($result->isSuccess());
        $this->assertSame(202, $result->getStatusCode());
    }

    /**
     * @covers ::post
     */
    public function testPostReturnsRetryableFailureOnConnectionError(): void
    {
        $request = new Request('POST', '/test');
        $this->mockGuzzleClient->shouldReceive('post')
            ->once()
            ->andThrow(new ConnectException('Connection refused', $request));

        $httpClient = new GuzzleHttpClient($this->mockGuzzleClient);
        $result = $httpClient->post('/test/endpoint');

        $this->assertTrue($result->isRetryable());
        $this->assertNull($result->getStatusCode());
        $this->assertStringContainsString('Connection refused', $result->getMessage());
    }

    /**
     * @covers ::post
     * @covers ::classifyHttpStatus
     */
    public function testPostReturnsRetryableFailureOn5xx(): void
    {
        $request = new Request('POST', '/test');
        $response = new Response(503);
        $this->mockGuzzleClient->shouldReceive('post')
            ->once()
            ->andThrow(new RequestException('Service Unavailable', $request, $response));

        $httpClient = new GuzzleHttpClient($this->mockGuzzleClient);
        $result = $httpClient->post('/test/endpoint');

        $this->assertTrue($result->isRetryable());
        $this->assertSame(503, $result->getStatusCode());
    }

    /**
     * @covers ::post
     * @covers ::classifyHttpStatus
     */
    public function testPostReturnsRetryableFailureOn429(): void
    {
        $request = new Request('POST', '/test');
        $response = new Response(429);
        $this->mockGuzzleClient->shouldReceive('post')
            ->once()
            ->andThrow(new RequestException('Too Many Requests', $request, $response));

        $httpClient = new GuzzleHttpClient($this->mockGuzzleClient);
        $result = $httpClient->post('/test/endpoint');

        $this->assertTrue($result->isRetryable());
        $this->assertSame(429, $result->getStatusCode());
    }

    /**
     * @covers ::post
     * @covers ::classifyHttpStatus
     */
    public function testPostReturnsPermanentFailureOn4xx(): void
    {
        $request = new Request('POST', '/test');
        $response = new Response(401);
        $this->mockGuzzleClient->shouldReceive('post')
            ->once()
            ->andThrow(new RequestException('Unauthorized', $request, $response));

        $httpClient = new GuzzleHttpClient($this->mockGuzzleClient);
        $result = $httpClient->post('/test/endpoint');

        $this->assertTrue($result->isPermanentFailure());
        $this->assertSame(401, $result->getStatusCode());
    }

    /**
     * @covers ::post
     */
    public function testPostReturnsRetryableFailureOnUnexpectedException(): void
    {
        $this->mockGuzzleClient->shouldReceive('post')
            ->once()
            ->andThrow(new \RuntimeException('Unexpected error'));

        $httpClient = new GuzzleHttpClient($this->mockGuzzleClient);
        $result = $httpClient->post('/test/endpoint');

        $this->assertTrue($result->isRetryable());
        $this->assertStringContainsString('Unexpected error', $result->getMessage());
    }

    /**
     * @covers ::post
     * @covers ::classifyHttpStatus
     */
    public function testPostReturnsRetryableOnRequestExceptionWithNoResponse(): void
    {
        $request = new Request('POST', '/test');
        $this->mockGuzzleClient->shouldReceive('post')
            ->once()
            ->andThrow(new RequestException('No response received', $request));

        $httpClient = new GuzzleHttpClient($this->mockGuzzleClient);
        $result = $httpClient->post('/test/endpoint');

        $this->assertTrue($result->isRetryable());
        $this->assertNull($result->getStatusCode());
    }

    /**
     * @covers ::post
     */
    public function testPostReturnsSuccessOn200(): void
    {
        $this->mockGuzzleClient->shouldReceive('post')
            ->once()
            ->andReturn(new Response(200));

        $httpClient = new GuzzleHttpClient($this->mockGuzzleClient);
        $result = $httpClient->post('/test/endpoint');

        $this->assertTrue($result->isSuccess());
        $this->assertSame(200, $result->getStatusCode());
    }

    /**
     * @covers ::post
     * @covers ::classifyHttpStatus
     */
    public function testPostReturnsPermanentFailureOn400(): void
    {
        $request = new Request('POST', '/test');
        $response = new Response(400);
        $this->mockGuzzleClient->shouldReceive('post')
            ->once()
            ->andThrow(new RequestException('Bad Request', $request, $response));

        $httpClient = new GuzzleHttpClient($this->mockGuzzleClient);
        $result = $httpClient->post('/test/endpoint');

        $this->assertTrue($result->isPermanentFailure());
        $this->assertSame(400, $result->getStatusCode());
    }

    /**
     * @covers ::post
     * @covers ::classifyHttpStatus
     */
    public function testPostReturnsRetryableOn500(): void
    {
        $request = new Request('POST', '/test');
        $response = new Response(500);
        $this->mockGuzzleClient->shouldReceive('post')
            ->once()
            ->andThrow(new RequestException('Internal Server Error', $request, $response));

        $httpClient = new GuzzleHttpClient($this->mockGuzzleClient);
        $result = $httpClient->post('/test/endpoint');

        $this->assertTrue($result->isRetryable());
        $this->assertSame(500, $result->getStatusCode());
    }
}
