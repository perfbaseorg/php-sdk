<?php

namespace Perfbase\SDK\Tests\Http;

use GuzzleHttp\Client as GuzzleClient;
use Mockery;
use Mockery\MockInterface;
use Perfbase\SDK\Http\GuzzleHttpClient;
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
    public function testPostSuccess(): void
    {
        $uri = '/test/endpoint';
        $options = ['headers' => ['Content-Type' => 'application/json']];
        
        $this->mockGuzzleClient->shouldReceive('post')
            ->once()
            ->with($uri, $options);
        
        $httpClient = new GuzzleHttpClient($this->mockGuzzleClient);
        
        $httpClient->post($uri, $options);
        
        $this->assertTrue(true); // Verify no exception was thrown
    }

    /**
     * @covers ::post
     */
    public function testPostWithEmptyOptions(): void
    {
        $uri = '/test/endpoint';
        
        $this->mockGuzzleClient->shouldReceive('post')
            ->once()
            ->with($uri, []);
        
        $httpClient = new GuzzleHttpClient($this->mockGuzzleClient);
        
        $httpClient->post($uri);
        
        $this->assertTrue(true); // Verify no exception was thrown
    }

    /**
     * @covers ::post
     */
    public function testPostSilentlyHandlesExceptions(): void
    {
        $uri = '/test/endpoint';
        $options = ['body' => 'test data'];
        
        $this->mockGuzzleClient->shouldReceive('post')
            ->once()
            ->with($uri, $options)
            ->andThrow(new \Exception('Network error'));
        
        $httpClient = new GuzzleHttpClient($this->mockGuzzleClient);
        
        // Should not throw exception (silent failure as per design)
        $httpClient->post($uri, $options);
        
        $this->assertTrue(true); // If we get here, exception was handled silently
    }

    /**
     * @covers ::post
     */
    public function testPostSilentlyHandlesGuzzleExceptions(): void
    {
        $uri = '/test/endpoint';
        
        $this->mockGuzzleClient->shouldReceive('post')
            ->once()
            ->with($uri, [])
            ->andThrow(new \GuzzleHttp\Exception\RequestException('Request failed', 
                Mockery::mock(\Psr\Http\Message\RequestInterface::class)));
        
        $httpClient = new GuzzleHttpClient($this->mockGuzzleClient);
        
        // Should not throw exception
        $httpClient->post($uri);
        
        $this->assertTrue(true);
    }
}