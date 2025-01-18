<?php

namespace Perfbase\SDK\Tests;

use Perfbase\SDK\Config;

/**
 * @coversDefaultClass \Perfbase\SDK\Config
 */
class ConfigTest extends BaseTest
{
    /**
     * Test the default configuration values
     * @covers ::__construct
     * @return void
     */
    public function testDefaultConfiguration(): void
    {
        $config = new Config();

        $this->assertNull($config->api_key);
        $this->assertSame('https://receiver.perfbase.com', $config->api_url);
        $this->assertSame([], $config->ignored_functions);
        $this->assertFalse($config->use_coarse_clock);
        $this->assertFalse($config->track_exceptions);
        $this->assertTrue($config->track_file_compilation);
        $this->assertFalse($config->track_memory_allocation);
        $this->assertTrue($config->track_cpu_time);
        $this->assertFalse($config->track_file_definitions);
        $this->assertTrue($config->track_pdo);
        $this->assertTrue($config->track_http);
        $this->assertTrue($config->track_caches);
        $this->assertTrue($config->track_mongodb);
        $this->assertTrue($config->track_elasticsearch);
        $this->assertTrue($config->track_queues);
        $this->assertTrue($config->track_aws_sdk);
        $this->assertTrue($config->track_file_operations);
        $this->assertNull($config->proxy);
        $this->assertSame(10, $config->timeout);
    }

    /**
     * Test the constructor sets the properties correctly
     * @covers ::__construct
     * @return void
     */
    public function testConstructorSetsProperties(): void
    {
        $config = new Config(
            'test_api_key',
            'https://custom.url',
            ['func1', 'func2'],
            true,
            true,
            false,
            true,
            false,
            true,
            false,
            false,
            true,
            false,
            true,
            false,
            true,
            false,
            'http://proxy:8080',
            1000,
            false
        );

        $this->assertSame('test_api_key', $config->api_key);
        $this->assertSame('https://custom.url', $config->api_url);
        $this->assertSame(['func1', 'func2'], $config->ignored_functions);
        $this->assertTrue($config->use_coarse_clock);
        $this->assertTrue($config->track_exceptions);
        $this->assertFalse($config->track_file_compilation);
        $this->assertTrue($config->track_memory_allocation);
        $this->assertFalse($config->track_cpu_time);
        $this->assertTrue($config->track_file_definitions);
        $this->assertFalse($config->track_pdo);
        $this->assertFalse($config->track_http);
        $this->assertTrue($config->track_caches);
        $this->assertFalse($config->track_mongodb);
        $this->assertTrue($config->track_elasticsearch);
        $this->assertFalse($config->track_queues);
        $this->assertTrue($config->track_aws_sdk);
        $this->assertFalse($config->track_file_operations);
        $this->assertSame('http://proxy:8080', $config->proxy);
        $this->assertSame(1000, $config->timeout);
    }

    /**
     * Test the fromArray method sets the properties correctly
     * @covers ::fromArray
     * @return void
     */
    public function testFromArray(): void
    {
        $configArray = [
            'api_key' => 'array_api_key',
            'api_url' => 'https://array.url',
            'ignored_functions' => ['func3'],
            'use_coarse_clock' => true,
            'track_http' => false
        ];

        $config = Config::fromArray($configArray);

        $this->assertSame('array_api_key', $config->api_key);
        $this->assertSame('https://array.url', $config->api_url);
        $this->assertSame(['func3'], $config->ignored_functions);
        $this->assertTrue($config->use_coarse_clock);
        $this->assertFalse($config->track_http);
    }

    /**
     * Test the getFlag method returns the correct flag
     * @covers ::getFlag
     * @return void
     */
    public function testGetFlag(): void
    {
        $config = Config::fromArray([
            'use_coarse_clock' => 0,
            'track_exceptions' => 0,
            'track_file_compilation' => 0,
            'track_memory_allocation' => 0,
            'track_cpu_time' => 0,
            'track_file_definitions' => 0,
            'track_pdo' => 0,
            'track_http' => 0,
            'track_caches' => 0,
            'track_mongodb' => 0,
            'track_elasticsearch' => 0,
            'track_queues' => 0,
            'track_aws_sdk' => 0,
            'track_file_operations' => 0,
        ]);

        // Should have zero by now
        $this->assertSame(0, $config->getFlag());


        $config->use_coarse_clock = true;
        $config->track_file_compilation = true;
        $config->track_cpu_time = true;
        $config->track_aws_sdk = true;

        $expectedFlag = 1 | 4 | 32 | 8192; // use_coarse_clock | track_file_compilation | track_cpu_time

        $this->assertSame($expectedFlag, $config->getFlag());
    }
}
