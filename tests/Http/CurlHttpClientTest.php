<?php

namespace Perfbase\SDK\Tests\Http;

use Perfbase\SDK\Http\CurlHttpClient;
use Perfbase\SDK\SubmitResult;
use Perfbase\SDK\Tests\BaseTest;
use RuntimeException;

/**
 * @coversDefaultClass \Perfbase\SDK\Http\CurlHttpClient
 */
class CurlHttpClientTest extends BaseTest
{
    /**
     * @covers ::post
     */
    public function testPostReturnsSuccessOn2xx(): void
    {
        $httpClient = new CurlHttpClient('https://example.com', 10, null, function (): array {
            return ['status_code' => 202, 'body' => 'accepted'];
        });

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
        $httpClient = new CurlHttpClient('https://example.com', 10, null, function (): array {
            throw new RuntimeException('Connection refused');
        });

        $result = $httpClient->post('/test/endpoint');

        $this->assertTrue($result->isRetryable());
        $this->assertNull($result->getStatusCode());
        $this->assertStringContainsString('Connection refused', $result->getMessage());
    }

    /**
     * @covers ::post
     */
    public function testPostReturnsRetryableFailureOn5xx(): void
    {
        $httpClient = new CurlHttpClient('https://example.com', 10, null, function (): array {
            return ['status_code' => 503, 'body' => 'Service Unavailable'];
        });

        $result = $httpClient->post('/test/endpoint');

        $this->assertTrue($result->isRetryable());
        $this->assertSame(503, $result->getStatusCode());
    }

    /**
     * @covers ::post
     */
    public function testPostReturnsRetryableFailureOn429(): void
    {
        $httpClient = new CurlHttpClient('https://example.com', 10, null, function (): array {
            return ['status_code' => 429, 'body' => 'Too Many Requests'];
        });

        $result = $httpClient->post('/test/endpoint');

        $this->assertTrue($result->isRetryable());
        $this->assertSame(429, $result->getStatusCode());
    }

    /**
     * @covers ::post
     */
    public function testPostReturnsPermanentFailureOn4xx(): void
    {
        $httpClient = new CurlHttpClient('https://example.com', 10, null, function (): array {
            return ['status_code' => 401, 'body' => 'Unauthorized'];
        });

        $result = $httpClient->post('/test/endpoint');

        $this->assertTrue($result->isPermanentFailure());
        $this->assertSame(401, $result->getStatusCode());
    }

    /**
     * @covers ::createCurlOptions
     */
    public function testCreateCurlOptionsIncludesExpectedRequestMetadata(): void
    {
        $httpClient = new CurlHttpClient('https://example.com', 15);

        $options = $this->invokePrivateMethod($httpClient, 'createCurlOptions', [[
            'url' => 'https://example.com/v1/submit',
            'headers' => [
                'Authorization' => 'Bearer test',
                'Content-Type' => 'application/octet-stream',
            ],
            'body' => 'binary-data',
            'timeout' => 15,
            'proxy' => null,
        ]]);

        $this->assertSame('https://example.com/v1/submit', $options[CURLOPT_URL]);
        $this->assertSame('binary-data', $options[CURLOPT_POSTFIELDS]);
        $this->assertSame(15, $options[CURLOPT_TIMEOUT]);
        $this->assertContains('Authorization: Bearer test', $options[CURLOPT_HTTPHEADER]);
        $this->assertContains('Content-Type: application/octet-stream', $options[CURLOPT_HTTPHEADER]);
    }

    /**
     * @covers ::createCurlOptions
     */
    public function testCreateCurlOptionsMapsHttpProxy(): void
    {
        $httpClient = new CurlHttpClient('https://example.com', 15, 'http://proxy.example.com:8080');

        $options = $this->invokePrivateMethod($httpClient, 'createCurlOptions', [[
            'url' => 'https://example.com/v1/submit',
            'headers' => [],
            'body' => '',
            'timeout' => 15,
            'proxy' => 'http://proxy.example.com:8080',
        ]]);

        $this->assertSame('proxy.example.com:8080', $options[CURLOPT_PROXY]);
        $this->assertSame(CURLPROXY_HTTP, $options[CURLOPT_PROXYTYPE]);
    }

    /**
     * @covers ::createCurlOptions
     */
    public function testCreateCurlOptionsMapsHttpsProxy(): void
    {
        $httpClient = new CurlHttpClient('https://example.com', 15, 'https://proxy.example.com:8443');

        $options = $this->invokePrivateMethod($httpClient, 'createCurlOptions', [[
            'url' => 'https://example.com/v1/submit',
            'headers' => [],
            'body' => '',
            'timeout' => 15,
            'proxy' => 'https://proxy.example.com:8443',
        ]]);

        $this->assertSame('proxy.example.com:8443', $options[CURLOPT_PROXY]);
        $this->assertSame(defined('CURLPROXY_HTTPS') ? CURLPROXY_HTTPS : CURLPROXY_HTTP, $options[CURLOPT_PROXYTYPE]);
    }

    /**
     * @covers ::createCurlOptions
     */
    public function testCreateCurlOptionsMapsProxyCredentials(): void
    {
        $httpClient = new CurlHttpClient('https://example.com', 15, 'http://user:secret@proxy.example.com:8080');

        $options = $this->invokePrivateMethod($httpClient, 'createCurlOptions', [[
            'url' => 'https://example.com/v1/submit',
            'headers' => [],
            'body' => '',
            'timeout' => 15,
            'proxy' => 'http://user:secret@proxy.example.com:8080',
        ]]);

        $this->assertSame('proxy.example.com:8080', $options[CURLOPT_PROXY]);
        $this->assertSame('user:secret', $options[CURLOPT_PROXYUSERPWD]);
        $this->assertSame(CURLPROXY_HTTP, $options[CURLOPT_PROXYTYPE]);
    }

    /**
     * @covers ::createCurlOptions
     */
    public function testCreateCurlOptionsMapsSocks5Proxy(): void
    {
        $httpClient = new CurlHttpClient('https://example.com', 15, 'socks5://proxy.example.com:1080');

        $options = $this->invokePrivateMethod($httpClient, 'createCurlOptions', [[
            'url' => 'https://example.com/v1/submit',
            'headers' => [],
            'body' => '',
            'timeout' => 15,
            'proxy' => 'socks5://proxy.example.com:1080',
        ]]);

        $this->assertSame('proxy.example.com:1080', $options[CURLOPT_PROXY]);
        $this->assertSame(CURLPROXY_SOCKS5, $options[CURLOPT_PROXYTYPE]);
    }

    /**
     * @covers ::createCurlOptions
     */
    public function testCreateCurlOptionsMapsSocks5hProxy(): void
    {
        $httpClient = new CurlHttpClient('https://example.com', 15, 'socks5h://proxy.example.com:1080');

        $options = $this->invokePrivateMethod($httpClient, 'createCurlOptions', [[
            'url' => 'https://example.com/v1/submit',
            'headers' => [],
            'body' => '',
            'timeout' => 15,
            'proxy' => 'socks5h://proxy.example.com:1080',
        ]]);

        $this->assertSame('proxy.example.com:1080', $options[CURLOPT_PROXY]);
        $this->assertSame(
            defined('CURLPROXY_SOCKS5_HOSTNAME') ? CURLPROXY_SOCKS5_HOSTNAME : CURLPROXY_SOCKS5,
            $options[CURLOPT_PROXYTYPE]
        );
    }
}
