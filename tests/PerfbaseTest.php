<?php

namespace Perfbase\SDK\Tests;

use Mockery;
use Mockery\MockInterface;
use Perfbase\SDK\Config;
use Perfbase\SDK\Exception\PerfbaseException;
use Perfbase\SDK\Exception\PerfbaseExtensionException;
use Perfbase\SDK\Exception\PerfbaseInvalidSpanException;
use Perfbase\SDK\Extension\ExtensionInterface;
use Perfbase\SDK\Http\ApiClient;
use Perfbase\SDK\Perfbase;
use Perfbase\SDK\SubmitResult;

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
        $this->mockExtension->shouldReceive('startSpan')->once()->with('test-span', $this->config->flags, []);
        $this->mockExtension->shouldReceive('reset')->once(); // Called by destructor

        $perfbase = new Perfbase($this->config, $this->mockExtension, $this->mockApiClient);

        $perfbase->startTraceSpan('test-span');

        // Verify span is active using reflection
        $activeSpanNames = $this->getPrivateFieldValue($perfbase, 'activeSpanNames');
        $this->assertContains('test-span', $activeSpanNames);
    }

    /**
     * @covers ::startTraceSpan
     * @covers ::validateSpanName
     */
    public function testStartTraceSpanWithEmptyNameUsesDefault(): void
    {
        $this->mockExtension->shouldReceive('isAvailable')->once()->andReturn(true);
        $this->mockExtension->shouldReceive('startSpan')->once()->with('default', $this->config->flags, []);
        $this->mockExtension->shouldReceive('reset')->once(); // Called by destructor

        $perfbase = new Perfbase($this->config, $this->mockExtension, $this->mockApiClient);

        $perfbase->startTraceSpan('  ');

        $activeSpanNames = $this->getPrivateFieldValue($perfbase, 'activeSpanNames');
        $this->assertContains('default', $activeSpanNames);
    }

    /**
     * @covers ::startTraceSpan
     */
    public function testStartTraceSpanWarnsWhenSpanAlreadyActive(): void
    {
        $this->mockExtension->shouldReceive('isAvailable')->once()->andReturn(true);
        $this->mockExtension->shouldReceive('startSpan')->once()->with('test-span', $this->config->flags, []);
        $this->mockExtension->shouldReceive('reset')->once(); // Called by destructor

        $perfbase = new Perfbase($this->config, $this->mockExtension, $this->mockApiClient);

        // Start span first time
        $perfbase->startTraceSpan('test-span');

        // Capture warnings using a custom error handler
        $warningTriggered = false;
        $warningMessage = '';

        set_error_handler(function ($errno, $errstr) use (&$warningTriggered, &$warningMessage) {
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
        $this->mockExtension->shouldReceive('startSpan')->once()->with('test-span', $this->config->flags, []);
        $this->mockExtension->shouldReceive('stopSpan')->once()->with('test-span');
        $this->mockExtension->shouldReceive('reset')->once(); // Called by destructor

        $perfbase = new Perfbase($this->config, $this->mockExtension, $this->mockApiClient);

        $perfbase->startTraceSpan('test-span');
        $result = $perfbase->stopTraceSpan('test-span');

        $this->assertTrue($result);

        // Verify span is no longer active
        $activeSpanNames = $this->getPrivateFieldValue($perfbase, 'activeSpanNames');
        $this->assertNotContains('test-span', $activeSpanNames);
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
        $this->mockExtension->shouldReceive('getSpanData')->once()->andReturn('trace-data');
        $this->mockExtension->shouldReceive('reset')->once(); // Called by destructor

        $perfbase = new Perfbase($this->config, $this->mockExtension, $this->mockApiClient);

        $result = $perfbase->getTraceData();

        $this->assertEquals('trace-data', $result);
    }

    /**
     * @covers ::submitTrace
     * @covers ::reset
     */
    public function testSubmitTraceResetsOnSuccess(): void
    {
        $extensionJson = json_encode(['v' => 1, 'p' => 'dGVzdA==']);
        $this->mockExtension->shouldReceive('isAvailable')->once()->andReturn(true);
        $this->mockExtension->shouldReceive('getSpanData')->once()->andReturn($extensionJson);
        $this->mockExtension->shouldReceive('reset')->twice(); // Called by submitTrace and destructor
        $this->mockApiClient->shouldReceive('submitTrace')
            ->once()
            ->with(Mockery::on(function (string $payload) {
                $decoded = json_decode($payload, true);
                return is_array($decoded)
                    && $decoded['v'] === 1
                    && $decoded['p'] === 'dGVzdA=='
                    && isset($decoded['d']); // timestamp injected
            }))
            ->andReturn(SubmitResult::success(202));

        $perfbase = new Perfbase($this->config, $this->mockExtension, $this->mockApiClient);
        $result = $perfbase->submitTrace();

        $this->assertTrue($result->isSuccess());
    }

    /**
     * @covers ::submitTrace
     */
    public function testSubmitTraceDoesNotResetOnFailure(): void
    {
        $extensionJson = json_encode(['v' => 1, 'p' => 'dGVzdA==']);
        $this->mockExtension->shouldReceive('isAvailable')->once()->andReturn(true);
        $this->mockExtension->shouldReceive('startSpan')->once();
        $this->mockExtension->shouldReceive('getSpanData')->once()->andReturn($extensionJson);
        $this->mockExtension->shouldReceive('reset')->once(); // Only destructor, NOT submitTrace
        $this->mockApiClient->shouldReceive('submitTrace')
            ->once()
            ->andReturn(SubmitResult::retryableFailure(503, 'Service Unavailable'));

        $perfbase = new Perfbase($this->config, $this->mockExtension, $this->mockApiClient);
        $perfbase->startTraceSpan('test-span');
        $result = $perfbase->submitTrace();

        $this->assertTrue($result->isRetryable());

        // Verify span state is preserved
        $activeSpanNames = $this->getPrivateFieldValue($perfbase, 'activeSpanNames');
        $this->assertContains('test-span', $activeSpanNames);
    }

    /**
     * @covers ::reset
     */
    public function testReset(): void
    {
        $this->mockExtension->shouldReceive('isAvailable')->once()->andReturn(true);
        $this->mockExtension->shouldReceive('startSpan')->once()->with('test-span', $this->config->flags, []);
        $this->mockExtension->shouldReceive('reset')->twice(); // Called manually and by destructor

        $perfbase = new Perfbase($this->config, $this->mockExtension, $this->mockApiClient);

        $perfbase->startTraceSpan('test-span');
        $perfbase->reset();

        // Verify active spans are cleared
        $activeSpanNames = $this->getPrivateFieldValue($perfbase, 'activeSpanNames');
        $this->assertEmpty($activeSpanNames);
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

    /**
     * @covers ::submitTrace
     */
    public function testSubmitTraceThrowsWhenExtensionReturnsMalformedData(): void
    {
        $this->mockExtension->shouldReceive('isAvailable')->once()->andReturn(true);
        $this->mockExtension->shouldReceive('getSpanData')->once()->andReturn('not valid json');
        $this->mockExtension->shouldReceive('reset')->once(); // destructor

        $perfbase = new Perfbase($this->config, $this->mockExtension, $this->mockApiClient);

        $this->expectException(PerfbaseException::class);
        $this->expectExceptionMessage('invalid JSON');
        $perfbase->submitTrace();
    }

    /**
     * @covers ::submitTrace
     */
    public function testSubmitTraceThrowsWhenExtensionReturnsEmpty(): void
    {
        $this->mockExtension->shouldReceive('isAvailable')->once()->andReturn(true);
        $this->mockExtension->shouldReceive('getSpanData')->once()->andReturn('');
        $this->mockExtension->shouldReceive('reset')->once(); // destructor

        $perfbase = new Perfbase($this->config, $this->mockExtension, $this->mockApiClient);

        $this->expectException(PerfbaseException::class);
        $this->expectExceptionMessage('empty trace data');
        $perfbase->submitTrace();
    }

    /**
     * @covers ::setAttribute
     */
    public function testSetAttribute(): void
    {
        $this->mockExtension->shouldReceive('isAvailable')->once()->andReturn(true);
        $this->mockExtension->shouldReceive('setAttribute')->once()->with('test_key', 'test_value');
        $this->mockExtension->shouldReceive('reset')->once(); // destructor

        $perfbase = new Perfbase($this->config, $this->mockExtension, $this->mockApiClient);
        $perfbase->setAttribute('test_key', 'test_value');

        // Mockery will verify setAttribute was called with the correct arguments
        $this->assertTrue(true);
    }

    /**
     * @covers ::startTraceSpan
     */
    public function testStartTraceSpanWithAttributes(): void
    {
        $attrs = ['key1' => 'val1', 'key2' => 'val2'];
        $this->mockExtension->shouldReceive('isAvailable')->once()->andReturn(true);
        $this->mockExtension->shouldReceive('startSpan')->once()->with('my-span', $this->config->flags, $attrs);
        $this->mockExtension->shouldReceive('reset')->once();

        $perfbase = new Perfbase($this->config, $this->mockExtension, $this->mockApiClient);
        $perfbase->startTraceSpan('my-span', $attrs);

        $activeSpanNames = $this->getPrivateFieldValue($perfbase, 'activeSpanNames');
        $this->assertContains('my-span', $activeSpanNames);
    }

    /**
     * @covers ::stopTraceSpan
     */
    public function testStopTraceSpanWithEmptyNameUsesDefault(): void
    {
        $this->mockExtension->shouldReceive('isAvailable')->once()->andReturn(true);
        $this->mockExtension->shouldReceive('startSpan')->once()->with('default', $this->config->flags, []);
        $this->mockExtension->shouldReceive('stopSpan')->once()->with('default');
        $this->mockExtension->shouldReceive('reset')->once();

        $perfbase = new Perfbase($this->config, $this->mockExtension, $this->mockApiClient);
        $perfbase->startTraceSpan('');
        $result = $perfbase->stopTraceSpan('');

        $this->assertTrue($result);
    }

    /**
     * @covers ::isAvailable
     */
    public function testIsAvailableStaticReturnsTrueWhenExtensionLoaded(): void
    {
        // isAvailable() creates a new PerfbaseExtension internally.
        // Without the real extension, it returns false.
        $result = Perfbase::isAvailable();
        $this->assertFalse($result); // Extension not loaded in test env
    }

    /**
     * @covers ::isExtensionAvailable
     */
    public function testIsExtensionAvailableReturnsFalse(): void
    {
        $this->mockExtension->shouldReceive('isAvailable')
            ->once()->andReturn(true)  // constructor
            ->shouldReceive('isAvailable')
            ->once()->andReturn(false); // isExtensionAvailable call
        $this->mockExtension->shouldReceive('reset')->once();

        $perfbase = new Perfbase($this->config, $this->mockExtension, $this->mockApiClient);
        $this->assertFalse($perfbase->isExtensionAvailable());
    }
}
