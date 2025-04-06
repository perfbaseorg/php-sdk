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
     * Test the constructor sets the properties correctly
     * @covers ::new
     * @return void
     * @throws PerfbaseInvalidConfigException
     */
    public function testConstructorSetsProperties(): void
    {
        $config = Config::new(
            'test_api_key',
            0,
            'https://custom.url',
            'http://proxy:8080',
            1000,
        );

        $this->assertSame('test_api_key', $config->api_key);
        $this->assertSame('https://custom.url', $config->api_url);
        $this->assertSame('http://proxy:8080', $config->proxy);
        $this->assertSame(1000, $config->timeout);
    }

    /**
     * Test the fromArray method sets the properties correctly
     * @covers ::fromArray
     * @return void
     * @throws PerfbaseInvalidConfigException
     */
    public function testFromArray(): void
    {
        $config = Config::fromArray([
            'api_key' => 'array_api_key',
            'api_url' => 'https://array.url',
        ]);
        $this->assertSame('array_api_key', $config->api_key);
        $this->assertSame('https://array.url', $config->api_url);
    }

    /**
     * @return void
     * @covers ::new
     */
    public function testThrowsExceptionIfApiKeyIsMissing(): void
    {
        $this->expectException(PerfbaseInvalidConfigException::class);
        Config::new();
    }

    /**
     * @return void
     * @covers ::new
     */
    public function testThrowsExceptionIfApiKeyIsNull(): void
    {
        $this->expectException(PerfbaseInvalidConfigException::class);
        Config::new(null, 0, 'http://example.com');
    }

    /**
     * @return void
     * @covers ::new
     */
    public function testThrowsExceptionIfUrlIsBlank(): void
    {
        $this->expectException(PerfbaseInvalidConfigException::class);
        Config::new('abc123', 0, '');
    }

    /**
     * @return void
     * @covers ::new
     */
    public function testThrowsExceptionIfUrlIsInvalid(): void
    {
        $this->expectException(PerfbaseInvalidConfigException::class);
        Config::new('abc123', 0, 'invalid-url');
    }

    /**
     * @return void
     * @covers ::new
     */
    public function testThrowsExceptionIfFlagsInvalidTooLow(): void
    {
        $this->expectException(PerfbaseInvalidConfigException::class);
        Config::new('abc123', -1);
    }

    /**
     * @return void
     * @covers ::new
     */
    public function testThrowsExceptionIfFlagsInvalidTooHigh(): void
    {
        $this->expectException(PerfbaseInvalidConfigException::class);
        Config::new('abc123', FeatureFlags::AllFlags + 1);
    }

}
