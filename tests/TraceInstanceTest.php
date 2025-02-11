<?php

namespace Perfbase\SDK\Tests;

use Perfbase\SDK\Config;
use Perfbase\SDK\Exception\PerfbaseInvalidConfigException;
use Perfbase\SDK\Http\ApiClient;
use Perfbase\SDK\Tracing\Attributes;
use Perfbase\SDK\Tracing\TraceInstance;
use Perfbase\SDK\Tracing\TraceState;
use ReflectionException;

/**
 * @coversDefaultClass TraceInstance
 */
class TraceInstanceTest extends BaseTest
{
    /**
     * @covers ::__construct
     * @return void
     */
    public function testThrowsExceptionIfApiKeyIsMissing(): void
    {
        $config = new Config();
        $this->expectException(PerfbaseInvalidConfigException::class);
        new TraceInstance($config);
    }

    /**
     * @covers ::__construct
     * @return void
     * @throws PerfbaseInvalidConfigException
     */
    public function testInitializesWithValidApiKey(): void
    {
        $config = new Config();
        $config->api_key = 'test_api';
        $traceInstance = new TraceInstance($config);
        $this->assertInstanceOf(TraceInstance::class, $traceInstance);
    }

    /**
     * @covers ::__construct
     * @return void
     * @throws PerfbaseInvalidConfigException
     * @throws ReflectionException
     */
    public function testHasValidAttributes(): void
    {
        $config = new Config();
        $config->api_key = 'test_api';

        $traceInstance = new TraceInstance($config);
        $this->assertObjectHasProperty('attributes', $traceInstance);
        $this->assertInstanceOf(Attributes::class, $traceInstance->attributes);
        $this->assertInstanceOf(Config::class, $this->getPrivateFieldValue($traceInstance, 'config'));
        $this->assertInstanceOf(ApiClient::class, $this->getPrivateFieldValue($traceInstance, 'apiClient'));
        $this->assertIsArray($this->getPrivateFieldValue($traceInstance, 'performanceData'));
        $this->assertIsArray($this->getPrivateFieldValue($traceInstance, 'metaData'));
        $this->assertInstanceOf(TraceState::class, $this->getPrivateFieldValue($traceInstance, 'state'));
    }

    /**
     * @covers ::__construct
     * @return void
     * @throws PerfbaseInvalidConfigException
     * @throws ReflectionException
     */
    public function testTransformsDataCorrectly(): void
    {
        $config = new Config();
        $config->api_key = 'test_api';
        $traceInstance = new TraceInstance($config);

        $perfInput = ['php~example~function' => [null, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14]];
        $metaInput = ['hello' => 'world'];

        // Test with empty performance data
        $this->setPrivateField($traceInstance, 'performanceData', $perfInput);
        $this->setPrivateField($traceInstance, 'metaData', $metaInput);

        // Transform data for sending
        $transformed = $traceInstance->transformData();

        // Decode the transformed data
        $decodedPerfData = json_decode(gzdecode(base64_decode($transformed['perf_data'])), true);
        $decodedMetaData = json_decode(gzdecode(base64_decode($transformed['meta_data'])), true);

        // Expected output
        $expectedPerfOutput = [
            "compressor" => "trie",
            "data" => [
                "glossary" => [
                    "php", "example", "function"
                ],
                "map" => [
                    "c" => [
                        [
                            "k" => [0],
                            "c" => [
                                [
                                    "k" => [1],
                                    "c" => [
                                        [
                                            "k" => [2],
                                            "c" => [],
                                            "v" => [null, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        // Assert that the transformed data is the same as the original data
        $this->assertEquals($decodedPerfData, $expectedPerfOutput);
        $this->assertEquals($metaInput, $decodedMetaData);

        // Double check previous assert for validity against invalid data
        $this->assertNotEquals($decodedPerfData, $decodedMetaData);
        $this->assertNotEquals($metaInput, $expectedPerfOutput);
    }


    /**
     * @covers ::__construct
     * @return void
     * @throws PerfbaseInvalidConfigException
     * @throws ReflectionException
     */
    public function testEncodesAndCompressesDataCorrectly(): void
    {
        $config = new Config();
        $config->api_key = 'test_api';
        $traceInstance = new TraceInstance($config);

        $sampleData = ['hello' => 'world', 'foo' => 'bar'];

        /** @var string $output */
        $result = $this->invokePrivateMethod($traceInstance, 'encodeAndCompressData', [$sampleData]);

        // Should be base64 string
        $this->assertIsString($result);

        // Should be gzipped json
        $result = gzdecode(base64_decode($result));

        // Should be json string again
        $this->assertIsString($result);

        // Should decode to original data
        /** @var array<string, string> $output */
        $output = json_decode($result, true);

        // Should be same as original data
        $this->assertEquals($sampleData, $output);
        $this->assertEquals($output['hello'], 'world');
        $this->assertEquals($output['foo'], 'bar');

    }
}
