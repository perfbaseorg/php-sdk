<?php

namespace Perfbase\SDK\Tests;

use Mockery;
use Mockery\MockInterface;
use Perfbase\SDK\Config;
use Perfbase\SDK\Exception\PerfbaseExtensionException;
use Perfbase\SDK\Exception\PerfbaseInvalidSpanException;
use Perfbase\SDK\Extension\ExtensionInterface;
use Perfbase\SDK\Http\ApiClient;
use Perfbase\SDK\Perfbase;

/**
 * @coversDefaultClass \Perfbase\SDK\Perfbase
 */
class PerfbaseTest extends BaseTest
{
    private MockInterface $mockExtension;
    private MockInterface $mockApiClient;
    private Config $config;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->mockExtension = Mockery::mock(ExtensionInterface::class);
        $this->mockApiClient = Mockery::mock(ApiClient::class);
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
     * @covers ::ensureIsAvailable
     */
    public function testConstructorWithAvailableExtension(): void
    {
        $this->mockExtension->shouldReceive('isAvailable')->once()->andReturn(true);
        $this->mockExtension->shouldReceive('reset')->once(); // Called by destructor
        
        $perfbase = new Perfbase($this->config, $this->mockExtension, $this->mockApiClient);
        
        $this->assertInstanceOf(Perfbase::class, $perfbase);
    }

    /**
     * @covers ::__construct
     * @covers ::ensureIsAvailable
     */
    public function testConstructorThrowsExceptionWhenExtensionUnavailable(): void
    {
        $this->mockExtension->shouldReceive('isAvailable')->once()->andReturn(false);
        
        $this->expectException(PerfbaseExtensionException::class);
        $this->expectExceptionMessage('Perfbase extension is not available.');
        
        new Perfbase($this->config, $this->mockExtension, $this->mockApiClient);
    }

    /**
     * @covers ::startTraceSpan
     * @covers ::validateSpanName
     */
    public function testStartTraceSpanWithValidName(): void
    {
        $this->mockExtension->shouldReceive('isAvailable')->once()->andReturn(true);
        $this->mockExtension->shouldReceive('enable')->once()->with('test-span', $this->config->flags);
        $this->mockExtension->shouldReceive('reset')->once(); // Called by destructor
        
        $perfbase = new Perfbase($this->config, $this->mockExtension, $this->mockApiClient);
        
        $perfbase->startTraceSpan('test-span');
        
        // Verify span is active using reflection
        $activeSpans = $this->getPrivateFieldValue($perfbase, 'activeSpans');
        $this->assertTrue($activeSpans['test-span']);
    }

    /**
     * @covers ::startTraceSpan
     * @covers ::validateSpanName
     */
    public function testStartTraceSpanWithEmptyNameUsesDefault(): void
    {
        $this->mockExtension->shouldReceive('isAvailable')->once()->andReturn(true);
        $this->mockExtension->shouldReceive('enable')->once()->with('default', $this->config->flags);
        $this->mockExtension->shouldReceive('reset')->once(); // Called by destructor
        
        $perfbase = new Perfbase($this->config, $this->mockExtension, $this->mockApiClient);
        
        $perfbase->startTraceSpan('  ');
        
        $activeSpans = $this->getPrivateFieldValue($perfbase, 'activeSpans');
        $this->assertTrue($activeSpans['default']);
    }

    /**
     * @covers ::startTraceSpan
     */
    public function testStartTraceSpanWarnsWhenSpanAlreadyActive(): void
    {
        $this->mockExtension->shouldReceive('isAvailable')->once()->andReturn(true);
        $this->mockExtension->shouldReceive('enable')->once()->with('test-span', $this->config->flags);
        $this->mockExtension->shouldReceive('reset')->once(); // Called by destructor
        
        $perfbase = new Perfbase($this->config, $this->mockExtension, $this->mockApiClient);
        
        // Start span first time
        $perfbase->startTraceSpan('test-span');
        
        // Capture warnings using a custom error handler
        $warningTriggered = false;
        $warningMessage = '';
        
        set_error_handler(function($errno, $errstr) use (&$warningTriggered, &$warningMessage) {
            if ($errno === E_USER_WARNING) {
                $warningTriggered = true;
                $warningMessage = $errstr;
            }
            return true; // Suppress the warning
        });
        
        // Attempt to start same span again should trigger warning
        $perfbase->startTraceSpan('test-span');
        
        // Restore original error handler
        restore_error_handler();
        
        // Assert that warning was triggered with correct message
        $this->assertTrue($warningTriggered, 'Expected warning was not triggered');
        $this->assertStringContainsString('Perfbase: Attempted to start span "test-span" which is already active.', $warningMessage);
    }

    /**
     * @covers ::stopTraceSpan
     * @covers ::isSpanActive
     * @covers ::validateSpanName
     */
    public function testStopTraceSpanWhenActive(): void
    {
        $this->mockExtension->shouldReceive('isAvailable')->once()->andReturn(true);
        $this->mockExtension->shouldReceive('enable')->once()->with('test-span', $this->config->flags);
        $this->mockExtension->shouldReceive('disable')->once()->with('test-span');
        $this->mockExtension->shouldReceive('reset')->once(); // Called by destructor
        
        $perfbase = new Perfbase($this->config, $this->mockExtension, $this->mockApiClient);
        
        $perfbase->startTraceSpan('test-span');
        $result = $perfbase->stopTraceSpan('test-span');
        
        $this->assertTrue($result);
        
        // Verify span is no longer active
        $activeSpans = $this->getPrivateFieldValue($perfbase, 'activeSpans');
        $this->assertFalse(isset($activeSpans['test-span']));
    }

    /**
     * @covers ::stopTraceSpan
     * @covers ::isSpanActive
     */
    public function testStopTraceSpanWhenNotActive(): void
    {
        $this->mockExtension->shouldReceive('isAvailable')->once()->andReturn(true);
        $this->mockExtension->shouldReceive('reset')->once(); // Called by destructor
        
        $perfbase = new Perfbase($this->config, $this->mockExtension, $this->mockApiClient);
        
        $result = $perfbase->stopTraceSpan('non-existent-span');
        
        $this->assertFalse($result);
    }

    /**
     * @covers ::setFlags
     */
    public function testSetFlags(): void
    {
        $this->mockExtension->shouldReceive('isAvailable')->once()->andReturn(true);
        $this->mockExtension->shouldReceive('reset')->once(); // Called by destructor
        
        $perfbase = new Perfbase($this->config, $this->mockExtension, $this->mockApiClient);
        
        $perfbase->setFlags(1024);
        
        $config = $this->getPrivateFieldValue($perfbase, 'config');
        $this->assertEquals(1024, $config->flags);
    }

    /**
     * @covers ::getTraceData
     */
    public function testGetTraceData(): void
    {
        $this->mockExtension->shouldReceive('isAvailable')->once()->andReturn(true);
        $this->mockExtension->shouldReceive('getData')->once()->andReturn('trace-data');
        $this->mockExtension->shouldReceive('reset')->once(); // Called by destructor
        
        $perfbase = new Perfbase($this->config, $this->mockExtension, $this->mockApiClient);
        
        $result = $perfbase->getTraceData();
        
        $this->assertEquals('trace-data', $result);
    }

    /**
     * @covers ::submitTrace
     * @covers ::reset
     */
    public function testSubmitTrace(): void
    {
        $this->mockExtension->shouldReceive('isAvailable')->once()->andReturn(true);
        $this->mockExtension->shouldReceive('getData')->once()->andReturn('trace-data');
        $this->mockExtension->shouldReceive('reset')->twice(); // Called by submitTrace and destructor
        $this->mockApiClient->shouldReceive('submitTrace')->once()->with('trace-data');
        
        $perfbase = new Perfbase($this->config, $this->mockExtension, $this->mockApiClient);
        
        $perfbase->submitTrace();
        
        $this->assertTrue(true); // Verify submitTrace completed successfully
    }

    /**
     * @covers ::reset
     */
    public function testReset(): void
    {
        $this->mockExtension->shouldReceive('isAvailable')->once()->andReturn(true);
        $this->mockExtension->shouldReceive('enable')->once()->with('test-span', $this->config->flags);
        $this->mockExtension->shouldReceive('reset')->twice(); // Called manually and by destructor
        
        $perfbase = new Perfbase($this->config, $this->mockExtension, $this->mockApiClient);
        
        $perfbase->startTraceSpan('test-span');
        $perfbase->reset();
        
        // Verify active spans are cleared
        $activeSpans = $this->getPrivateFieldValue($perfbase, 'activeSpans');
        $this->assertEmpty($activeSpans);
    }

    /**
     * @covers ::isExtensionAvailable
     */
    public function testIsExtensionAvailable(): void
    {
        $this->mockExtension->shouldReceive('isAvailable')->twice()->andReturn(true);
        $this->mockExtension->shouldReceive('reset')->once(); // Called by destructor
        
        $perfbase = new Perfbase($this->config, $this->mockExtension, $this->mockApiClient);
        
        $this->assertTrue($perfbase->isExtensionAvailable());
    }

    /**
     * @covers ::__destruct
     */
    public function testDestructorCallsReset(): void
    {
        $this->mockExtension->shouldReceive('isAvailable')->once()->andReturn(true);
        $this->mockExtension->shouldReceive('reset')->once();
        
        $perfbase = new Perfbase($this->config, $this->mockExtension, $this->mockApiClient);
        
        // Trigger destructor
        unset($perfbase);
        
        $this->assertTrue(true); // Verify destructor was called without issues
    }
}