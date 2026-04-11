<?php

namespace Perfbase\SDK\Tests;

use Perfbase\SDK\Config;
use Perfbase\SDK\Exception\PerfbaseInvalidConfigException;
use Perfbase\SDK\FeatureFlags;

/**
 * @coversDefaultClass \Perfbase\SDK\Config
 */
class ConfigTest extends BaseTest
{
    /**
     * @covers ::new
     * @covers ::getApiKey
     * @covers ::getApiUrl
     * @covers ::getProxy
     * @covers ::getTimeout
     * @covers ::getFlags
     */
    public function testConstructorSetsProperties(): void
    {
        $config = Config::new(
            'test_api_key',
            0,
            'https://custom.url',
            'http://proxy:8080',
            1000
        );

        $this->assertSame('test_api_key', $config->getApiKey());
        $this->assertSame('https://custom.url', $config->getApiUrl());
        $this->assertSame('http://proxy:8080', $config->getProxy());
        $this->assertSame(1000, $config->getTimeout());
        $this->assertSame(0, $config->getFlags());
    }

    /**
     * @covers ::fromArray
     * @covers ::getApiKey
     * @covers ::getApiUrl
     */
    public function testFromArray(): void
    {
        $config = Config::fromArray([
            'api_key' => 'array_api_key',
            'api_url' => 'https://array.url',
        ]);

        $this->assertSame('array_api_key', $config->getApiKey());
        $this->assertSame('https://array.url', $config->getApiUrl());
    }

    /**
     * @covers ::new
     */
    public function testThrowsExceptionIfApiKeyIsMissing(): void
    {
        $this->expectException(PerfbaseInvalidConfigException::class);
        Config::new();
    }

    /**
     * @covers ::new
     */
    public function testThrowsExceptionIfApiKeyIsNull(): void
    {
        $this->expectException(PerfbaseInvalidConfigException::class);
        Config::new(null, 0, 'https://example.com');
    }

    /**
     * @covers ::new
     */
    public function testThrowsExceptionIfUrlIsBlank(): void
    {
        $this->expectException(PerfbaseInvalidConfigException::class);
        Config::new('abc123', 0, '');
    }

    /**
     * @covers ::new
     */
    public function testThrowsExceptionIfUrlIsInvalid(): void
    {
        $this->expectException(PerfbaseInvalidConfigException::class);
        Config::new('abc123', 0, 'invalid-url');
    }

    /**
     * @covers ::new
     */
    public function testThrowsExceptionIfApiUrlIsNotHttps(): void
    {
        $this->expectException(PerfbaseInvalidConfigException::class);
        $this->expectExceptionMessage('API URL must use HTTPS');
        Config::new('abc123', 0, 'http://example.com');
    }

    /**
     * @covers ::new
     */
    public function testThrowsExceptionIfFlagsInvalidTooLow(): void
    {
        $this->expectException(PerfbaseInvalidConfigException::class);
        Config::new('abc123', -1);
    }

    /**
     * @covers ::new
     */
    public function testThrowsExceptionIfFlagsInvalidTooHigh(): void
    {
        $this->expectException(PerfbaseInvalidConfigException::class);
        Config::new('abc123', FeatureFlags::AllFlags + 1);
    }

    /**
     * @covers ::validate
     */
    public function testThrowsExceptionIfTimeoutIsZero(): void
    {
        $this->expectException(PerfbaseInvalidConfigException::class);
        $this->expectExceptionMessage('Timeout must be a positive integer');
        Config::new('abc123', 0, null, null, 0);
    }

    /**
     * @covers ::validate
     */
    public function testThrowsExceptionIfTimeoutIsNegative(): void
    {
        $this->expectException(PerfbaseInvalidConfigException::class);
        Config::new('abc123', 0, null, null, -5);
    }

    /**
     * @covers ::fromArray
     */
    public function testFromArrayThrowsOnUnknownKey(): void
    {
        $this->expectException(PerfbaseInvalidConfigException::class);
        $this->expectExceptionMessage('Invalid configuration option');
        Config::fromArray(['api_key' => 'test', 'unknown_key' => 'value']);
    }

    /**
     * @covers ::fromArray
     */
    public function testFromArrayThrowsOnInvalidTimeoutType(): void
    {
        $this->expectException(PerfbaseInvalidConfigException::class);
        $this->expectExceptionMessage('Configuration option "timeout" must be an integer');
        Config::fromArray(['api_key' => 'test', 'timeout' => '12']);
    }

    /**
     * @covers ::fromArray
     */
    public function testFromArrayThrowsOnInvalidApiUrlType(): void
    {
        $this->expectException(PerfbaseInvalidConfigException::class);
        $this->expectExceptionMessage('Configuration option "api_url" must be a string');
        Config::fromArray(['api_key' => 'test', 'api_url' => null]);
    }

    /**
     * @covers ::fromArray
     */
    public function testFromArrayThrowsOnInvalidFlagsType(): void
    {
        $this->expectException(PerfbaseInvalidConfigException::class);
        $this->expectExceptionMessage('Configuration option "flags" must be an integer');
        Config::fromArray(['api_key' => 'test', 'flags' => [1, 2, 3]]);
    }

    /**
     * @covers ::fromArray
     */
    public function testFromArrayThrowsOnInvalidProxyType(): void
    {
        $this->expectException(PerfbaseInvalidConfigException::class);
        $this->expectExceptionMessage('Configuration option "proxy" must be a string');
        Config::fromArray(['api_key' => 'test', 'proxy' => ['bad']]);
    }

    /**
     * @covers ::fromArray
     */
    public function testFromArrayThrowsOnInvalidProxyUrl(): void
    {
        $this->expectException(PerfbaseInvalidConfigException::class);
        $this->expectExceptionMessage('Proxy URL is not valid');
        Config::fromArray(['api_key' => 'test', 'proxy' => 'not a url']);
    }

    /**
     * @covers ::fromArray
     */
    public function testFromArrayThrowsOnUnsupportedProxyScheme(): void
    {
        $this->expectException(PerfbaseInvalidConfigException::class);
        $this->expectExceptionMessage('Proxy URL must use http, https, socks5, or socks5h');
        Config::fromArray(['api_key' => 'test', 'proxy' => 'ftp://proxy.example.com:21']);
    }

    /**
     * @covers ::fromArray
     */
    public function testFromArrayAcceptsSupportedProxySchemes(): void
    {
        $httpConfig = Config::fromArray(['api_key' => 'test', 'proxy' => 'http://proxy.example.com:8080']);
        $socksConfig = Config::fromArray(['api_key' => 'test', 'proxy' => 'socks5h://proxy.example.com:1080']);

        $this->assertSame('http://proxy.example.com:8080', $httpConfig->getProxy());
        $this->assertSame('socks5h://proxy.example.com:1080', $socksConfig->getProxy());
    }

    /**
     * @covers ::fromArray
     * @covers ::getFlags
     */
    public function testFromArrayWithAllOptions(): void
    {
        $config = Config::fromArray([
            'api_key' => 'my-key',
            'api_url' => 'https://custom.api.com',
            'proxy' => 'http://proxy:8080',
            'timeout' => 30,
            'flags' => FeatureFlags::TrackCpuTime | FeatureFlags::TrackPdo,
        ]);

        $this->assertSame('my-key', $config->getApiKey());
        $this->assertSame('https://custom.api.com', $config->getApiUrl());
        $this->assertSame('http://proxy:8080', $config->getProxy());
        $this->assertSame(30, $config->getTimeout());
        $this->assertSame(FeatureFlags::TrackCpuTime | FeatureFlags::TrackPdo, $config->getFlags());
    }

    /**
     * @covers ::new
     */
    public function testNewWithDefaultValues(): void
    {
        $config = Config::new('my-key');

        $this->assertSame('my-key', $config->getApiKey());
        $this->assertSame('https://ingress.perfbase.cloud', $config->getApiUrl());
        $this->assertNull($config->getProxy());
        $this->assertSame(10, $config->getTimeout());
        $this->assertSame(FeatureFlags::DefaultFlags, $config->getFlags());
    }

    /**
     * @covers ::withFlags
     * @covers ::getFlags
     */
    public function testWithFlagsReturnsUpdatedClone(): void
    {
        $config = Config::new('my-key');
        $updated = $config->withFlags(FeatureFlags::TrackCpuTime);

        $this->assertNotSame($config, $updated);
        $this->assertSame(FeatureFlags::DefaultFlags, $config->getFlags());
        $this->assertSame(FeatureFlags::TrackCpuTime, $updated->getFlags());
    }
}
