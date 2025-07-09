<?php

namespace Perfbase\SDK\Tests\Integration;

use Mockery;
use Mockery\MockInterface;
use Perfbase\SDK\Config;
use Perfbase\SDK\Extension\ExtensionInterface;
use Perfbase\SDK\Http\ApiClient;
use Perfbase\SDK\Http\HttpClientInterface;
use Perfbase\SDK\Perfbase;
use Perfbase\SDK\Tests\BaseTest;

/**
 * Integration tests for the full Perfbase SDK workflow
 */
class PerfbaseIntegrationTest extends BaseTest
{
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
        // Setup extension expectations
        $this->mockExtension->shouldReceive('isAvailable')->once()->andReturn(true);
        $this->mockExtension->shouldReceive('enable')->once()->with('integration-span', $this->config->flags);
        $this->mockExtension->shouldReceive('disable')->once()->with('integration-span');
        $this->mockExtension->shouldReceive('getData')->twice()->andReturn('integration-trace-data'); // Called by getTraceData and submitTrace
        $this->mockExtension->shouldReceive('reset')->twice(); // Called by submitTrace and destructor
        
        // Setup API client expectations
        $this->mockApiClient->shouldReceive('submitTrace')->once()->with('integration-trace-data');
        
        // Create Perfbase instance
        $perfbase = new Perfbase($this->config, $this->mockExtension, $this->mockApiClient);
        
        // Execute complete workflow
        $perfbase->startTraceSpan('integration-span');
        
        // Simulate some work happening...
        
        $stopResult = $perfbase->stopTraceSpan('integration-span');
        $this->assertTrue($stopResult);
        
        // Get and verify trace data
        $traceData = $perfbase->getTraceData();
        $this->assertEquals('integration-trace-data', $traceData);
        
        // Submit trace
        $perfbase->submitTrace();
    }

    /**
     * Test multiple spans workflow
     * @covers \Perfbase\SDK\Perfbase
     */
    public function testMultipleSpansWorkflow(): void
    {
        $this->mockExtension->shouldReceive('isAvailable')->once()->andReturn(true);
        
        // First span
        $this->mockExtension->shouldReceive('enable')->once()->with('span-1', $this->config->flags);
        $this->mockExtension->shouldReceive('disable')->once()->with('span-1');
        
        // Second span
        $this->mockExtension->shouldReceive('enable')->once()->with('span-2', $this->config->flags);
        $this->mockExtension->shouldReceive('disable')->once()->with('span-2');
        
        // Data retrieval and submission
        $this->mockExtension->shouldReceive('getData')->once()->andReturn('multi-span-data');
        $this->mockExtension->shouldReceive('reset')->twice(); // Called by submitTrace and destructor
        $this->mockApiClient->shouldReceive('submitTrace')->once()->with('multi-span-data');
        
        $perfbase = new Perfbase($this->config, $this->mockExtension, $this->mockApiClient);
        
        // Start multiple spans
        $perfbase->startTraceSpan('span-1');
        $perfbase->startTraceSpan('span-2');
        
        // Stop spans in different order
        $result2 = $perfbase->stopTraceSpan('span-2');
        $result1 = $perfbase->stopTraceSpan('span-1');
        
        $this->assertTrue($result1);
        $this->assertTrue($result2);
        
        // Submit combined trace
        $perfbase->submitTrace();
    }

    /**
     * Test workflow with configuration changes
     * @covers \Perfbase\SDK\Perfbase
     */
    public function testWorkflowWithConfigurationChanges(): void
    {
        $this->mockExtension->shouldReceive('isAvailable')->once()->andReturn(true);
        
        // Initial span with default flags
        $this->mockExtension->shouldReceive('enable')->once()->with('config-span', $this->config->flags);
        
        // After flag change
        $newFlags = 2048;
        $this->mockExtension->shouldReceive('enable')->once()->with('modified-span', $newFlags);
        $this->mockExtension->shouldReceive('disable')->once()->with('config-span');
        $this->mockExtension->shouldReceive('disable')->once()->with('modified-span');
        $this->mockExtension->shouldReceive('getData')->once()->andReturn('config-change-data');
        $this->mockExtension->shouldReceive('reset')->twice(); // Called by submitTrace and destructor
        $this->mockApiClient->shouldReceive('submitTrace')->once()->with('config-change-data');
        
        $perfbase = new Perfbase($this->config, $this->mockExtension, $this->mockApiClient);
        
        // Start span with initial configuration
        $perfbase->startTraceSpan('config-span');
        
        // Change flags
        $perfbase->setFlags($newFlags);
        
        // Start another span with new flags
        $perfbase->startTraceSpan('modified-span');
        
        // Stop both spans
        $perfbase->stopTraceSpan('config-span');
        $perfbase->stopTraceSpan('modified-span');
        
        // Submit trace
        $perfbase->submitTrace();
        
        $this->assertTrue(true); // Verify workflow completed successfully
    }

    /**
     * Test error handling in workflow
     * @covers \Perfbase\SDK\Perfbase
     */
    public function testErrorHandlingInWorkflow(): void
    {
        $this->mockExtension->shouldReceive('isAvailable')->once()->andReturn(true);
        $this->mockExtension->shouldReceive('enable')->once()->with('error-span', $this->config->flags);
        $this->mockExtension->shouldReceive('reset')->once(); // Called by destructor
        
        $perfbase = new Perfbase($this->config, $this->mockExtension, $this->mockApiClient);
        
        // Start a span
        $perfbase->startTraceSpan('error-span');
        
        // Try to stop a non-existent span
        $result = $perfbase->stopTraceSpan('non-existent-span');
        $this->assertFalse($result);
        
        // Properly stop the actual span
        $this->mockExtension->shouldReceive('disable')->once()->with('error-span');
        $result = $perfbase->stopTraceSpan('error-span');
        $this->assertTrue($result);
    }

    /**
     * Test ApiClient integration with real HTTP client interface
     * @covers \Perfbase\SDK\Http\ApiClient
     */
    public function testApiClientIntegration(): void
    {
        $testData = 'api-integration-data';
        
        $this->mockHttpClient->shouldReceive('post')
            ->once()
            ->with('/v1/submit', Mockery::on(function ($options) use ($testData) {
                return $options['body'] === $testData
                    && isset($options['headers']['Authorization'])
                    && $options['headers']['Authorization'] === 'Bearer integration-test-key';
            }));
        
        $apiClient = new ApiClient($this->config, $this->mockHttpClient);
        
        $apiClient->submitTrace($testData);
        
        $this->assertTrue(true); // Verify no exception was thrown
    }

    /**
     * Test full stack integration with API client
     * @covers \Perfbase\SDK\Perfbase
     */
    public function testFullStackIntegration(): void
    {
        $this->mockExtension->shouldReceive('isAvailable')->once()->andReturn(true);
        $this->mockExtension->shouldReceive('enable')->once()->with('full-stack', $this->config->flags);
        $this->mockExtension->shouldReceive('disable')->once()->with('full-stack');
        $this->mockExtension->shouldReceive('getData')->once()->andReturn('full-stack-data');
        $this->mockExtension->shouldReceive('reset')->twice(); // Called by submitTrace and destructor
        
        $this->mockHttpClient->shouldReceive('post')
            ->once()
            ->with('/v1/submit', Mockery::on(function ($options) {
                return $options['body'] === 'full-stack-data'
                    && isset($options['headers']['Authorization']);
            }));
        
        // Create real API client with mocked HTTP client
        $apiClient = new ApiClient($this->config, $this->mockHttpClient);
        
        $perfbase = new Perfbase($this->config, $this->mockExtension, $apiClient);
        
        // Execute full workflow
        $perfbase->startTraceSpan('full-stack');
        $perfbase->stopTraceSpan('full-stack');
        $perfbase->submitTrace();
        
        $this->assertTrue(true); // Verify full stack integration completed
    }

    /**
     * Test cleanup behavior
     * @covers \Perfbase\SDK\Perfbase
     */
    public function testCleanupBehavior(): void
    {
        $this->mockExtension->shouldReceive('isAvailable')->once()->andReturn(true);
        $this->mockExtension->shouldReceive('enable')->once()->with('cleanup-span', $this->config->flags);
        $this->mockExtension->shouldReceive('reset')->twice(); // Once manual, once destructor
        
        $perfbase = new Perfbase($this->config, $this->mockExtension, $this->mockApiClient);
        
        $perfbase->startTraceSpan('cleanup-span');
        
        // Manual reset
        $perfbase->reset();
        
        // Verify active spans are cleared
        $activeSpans = $this->getPrivateFieldValue($perfbase, 'activeSpans');
        $this->assertEmpty($activeSpans);
        
        // Destructor should also call reset (verified by mock expectation)
        unset($perfbase);
    }
}