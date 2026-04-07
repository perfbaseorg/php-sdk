<?php

namespace Perfbase\SDK\Tests\Integration;

use Mockery;
use Mockery\MockInterface;
use Perfbase\SDK\Config;
use Perfbase\SDK\Extension\ExtensionInterface;
use Perfbase\SDK\Http\ApiClient;
use Perfbase\SDK\Http\HttpClientInterface;
use Perfbase\SDK\Perfbase;
use Perfbase\SDK\SubmitResult;
use Perfbase\SDK\Tests\BaseTest;

/**
 * Integration tests for the full Perfbase SDK workflow
 */
class PerfbaseIntegrationTest extends BaseTest
{
    /** Valid extension JSON output for use in tests that call submitTrace() */
    private const EXTENSION_JSON = '{"v":1,"p":"dGVzdA=="}';

    private MockInterface $mockExtension;
    private MockInterface $mockHttpClient;
    private MockInterface $mockApiClient;
    private Config $config;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockExtension = Mockery::mock(ExtensionInterface::class);
        $this->mockHttpClient = Mockery::mock(HttpClientInterface::class);
        $this->mockApiClient = Mockery::mock(ApiClient::class);

        $this->config = Config::fromArray([
            'api_key' => 'integration-test-key',
            'api_url' => 'https://integration.test.com',
            'timeout' => 15
        ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test complete profiling workflow from start to submission
     * @covers \Perfbase\SDK\Perfbase
     */
    public function testCompleteProfilingWorkflow(): void
    {
        $this->mockExtension->shouldReceive('isAvailable')->once()->andReturn(true);
        $this->mockExtension->shouldReceive('startSpan')->once()->with('integration-span', $this->config->flags, []);
        $this->mockExtension->shouldReceive('stopSpan')->once()->with('integration-span');
        $this->mockExtension->shouldReceive('getSpanData')->twice()->andReturn(self::EXTENSION_JSON);
        $this->mockExtension->shouldReceive('reset')->twice();

        $this->mockApiClient->shouldReceive('submitTrace')
            ->once()
            ->with(Mockery::on(function (string $p): bool {
                return strpos($p, '"v":1') !== false;
            }))
            ->andReturn(SubmitResult::success(202));

        $perfbase = new Perfbase($this->config, $this->mockExtension, $this->mockApiClient);

        $perfbase->startTraceSpan('integration-span');
        $this->assertTrue($perfbase->stopTraceSpan('integration-span'));

        $traceData = $perfbase->getTraceData();
        $this->assertEquals(self::EXTENSION_JSON, $traceData);

        $result = $perfbase->submitTrace();
        $this->assertTrue($result->isSuccess());
    }

    /**
     * Test multiple spans workflow
     * @covers \Perfbase\SDK\Perfbase
     */
    public function testMultipleSpansWorkflow(): void
    {
        $this->mockExtension->shouldReceive('isAvailable')->once()->andReturn(true);
        $this->mockExtension->shouldReceive('startSpan')->once()->with('span-1', $this->config->flags, []);
        $this->mockExtension->shouldReceive('stopSpan')->once()->with('span-1');
        $this->mockExtension->shouldReceive('startSpan')->once()->with('span-2', $this->config->flags, []);
        $this->mockExtension->shouldReceive('stopSpan')->once()->with('span-2');
        $this->mockExtension->shouldReceive('getSpanData')->once()->andReturn(self::EXTENSION_JSON);
        $this->mockExtension->shouldReceive('reset')->twice();

        $this->mockApiClient->shouldReceive('submitTrace')
            ->once()
            ->andReturn(SubmitResult::success());

        $perfbase = new Perfbase($this->config, $this->mockExtension, $this->mockApiClient);

        $perfbase->startTraceSpan('span-1');
        $perfbase->startTraceSpan('span-2');
        $this->assertTrue($perfbase->stopTraceSpan('span-2'));
        $this->assertTrue($perfbase->stopTraceSpan('span-1'));

        $this->assertTrue($perfbase->submitTrace()->isSuccess());
    }

    /**
     * Test workflow with configuration changes
     * @covers \Perfbase\SDK\Perfbase
     */
    public function testWorkflowWithConfigurationChanges(): void
    {
        $this->mockExtension->shouldReceive('isAvailable')->once()->andReturn(true);
        $this->mockExtension->shouldReceive('startSpan')->once()->with('config-span', $this->config->flags, []);

        $newFlags = 2048;
        $this->mockExtension->shouldReceive('startSpan')->once()->with('modified-span', $newFlags, []);
        $this->mockExtension->shouldReceive('stopSpan')->once()->with('config-span');
        $this->mockExtension->shouldReceive('stopSpan')->once()->with('modified-span');
        $this->mockExtension->shouldReceive('getSpanData')->once()->andReturn(self::EXTENSION_JSON);
        $this->mockExtension->shouldReceive('reset')->twice();

        $this->mockApiClient->shouldReceive('submitTrace')
            ->once()
            ->andReturn(SubmitResult::success());

        $perfbase = new Perfbase($this->config, $this->mockExtension, $this->mockApiClient);

        $perfbase->startTraceSpan('config-span');
        $perfbase->setFlags($newFlags);
        $perfbase->startTraceSpan('modified-span');
        $perfbase->stopTraceSpan('config-span');
        $perfbase->stopTraceSpan('modified-span');

        $this->assertTrue($perfbase->submitTrace()->isSuccess());
    }

    /**
     * Test error handling in workflow
     * @covers \Perfbase\SDK\Perfbase
     */
    public function testErrorHandlingInWorkflow(): void
    {
        $this->mockExtension->shouldReceive('isAvailable')->once()->andReturn(true);
        $this->mockExtension->shouldReceive('startSpan')->once()->with('error-span', $this->config->flags, []);
        $this->mockExtension->shouldReceive('reset')->once();

        $perfbase = new Perfbase($this->config, $this->mockExtension, $this->mockApiClient);

        $perfbase->startTraceSpan('error-span');

        $this->assertFalse($perfbase->stopTraceSpan('non-existent-span'));

        $this->mockExtension->shouldReceive('stopSpan')->once()->with('error-span');
        $this->assertTrue($perfbase->stopTraceSpan('error-span'));
    }

    /**
     * Test ApiClient integration with HttpClientInterface
     * @covers \Perfbase\SDK\Http\ApiClient
     */
    public function testApiClientIntegration(): void
    {
        $testData = '{"v":1,"p":"test","d":"2024-01-01T00:00:00Z"}';

        $this->mockHttpClient->shouldReceive('post')
            ->once()
            ->with('/v1/submit', Mockery::on(function ($options) use ($testData) {
                return $options['body'] === $testData
                    && $options['headers']['Authorization'] === 'Bearer integration-test-key';
            }))
            ->andReturn(SubmitResult::success(202));

        $apiClient = new ApiClient($this->config, $this->mockHttpClient);
        $this->assertTrue($apiClient->submitTrace($testData)->isSuccess());
    }

    /**
     * Test full stack integration: Perfbase → TracePayloadFactory → ApiClient → HttpClient
     * @covers \Perfbase\SDK\Perfbase
     */
    public function testFullStackIntegration(): void
    {
        $this->mockExtension->shouldReceive('isAvailable')->once()->andReturn(true);
        $this->mockExtension->shouldReceive('startSpan')->once()->with('full-stack', $this->config->flags, []);
        $this->mockExtension->shouldReceive('stopSpan')->once()->with('full-stack');
        $this->mockExtension->shouldReceive('getSpanData')->once()->andReturn(self::EXTENSION_JSON);
        $this->mockExtension->shouldReceive('reset')->twice();

        $this->mockHttpClient->shouldReceive('post')
            ->once()
            ->with('/v1/submit', Mockery::on(function ($options) {
                $body = json_decode($options['body'], true);
                // Factory should have injected 'd' timestamp
                return is_array($body)
                    && $body['v'] === 1
                    && $body['p'] === 'dGVzdA=='
                    && isset($body['d'])
                    && isset($options['headers']['Authorization']);
            }))
            ->andReturn(SubmitResult::success(202));

        $apiClient = new ApiClient($this->config, $this->mockHttpClient);
        $perfbase = new Perfbase($this->config, $this->mockExtension, $apiClient);

        $perfbase->startTraceSpan('full-stack');
        $perfbase->stopTraceSpan('full-stack');
        $this->assertTrue($perfbase->submitTrace()->isSuccess());
    }

    /**
     * Test that failed submission preserves trace state
     * @covers \Perfbase\SDK\Perfbase
     */
    public function testFailedSubmissionPreservesState(): void
    {
        $this->mockExtension->shouldReceive('isAvailable')->once()->andReturn(true);
        $this->mockExtension->shouldReceive('startSpan')->once();
        $this->mockExtension->shouldReceive('stopSpan')->once();
        $this->mockExtension->shouldReceive('getSpanData')->andReturn(self::EXTENSION_JSON);
        $this->mockExtension->shouldReceive('reset')->once(); // destructor only

        $this->mockHttpClient->shouldReceive('post')
            ->once()
            ->andReturn(SubmitResult::retryableFailure(503, 'Service Unavailable'));

        $apiClient = new ApiClient($this->config, $this->mockHttpClient);
        $perfbase = new Perfbase($this->config, $this->mockExtension, $apiClient);

        $perfbase->startTraceSpan('failing-span');
        $perfbase->stopTraceSpan('failing-span');
        $result = $perfbase->submitTrace();

        $this->assertTrue($result->isRetryable());
        $this->assertSame(503, $result->getStatusCode());
    }

    /**
     * Test cleanup behavior
     * @covers \Perfbase\SDK\Perfbase
     */
    public function testCleanupBehavior(): void
    {
        $this->mockExtension->shouldReceive('isAvailable')->once()->andReturn(true);
        $this->mockExtension->shouldReceive('startSpan')->once()->with('cleanup-span', $this->config->flags, []);
        $this->mockExtension->shouldReceive('reset')->twice();

        $perfbase = new Perfbase($this->config, $this->mockExtension, $this->mockApiClient);

        $perfbase->startTraceSpan('cleanup-span');
        $perfbase->reset();

        $activeSpanNames = $this->getPrivateFieldValue($perfbase, 'activeSpanNames');
        $this->assertEmpty($activeSpanNames);

        unset($perfbase);
    }
}
