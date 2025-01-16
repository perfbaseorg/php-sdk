<?php

namespace Tests;

use Perfbase\SDK\Exception\PerfbaseStateException;
use Perfbase\SDK\Tracing\TraceState;

/**
 * @coversDefaultClass \Perfbase\SDK\Tracing\TraceState
 */
class TraceStateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers ::__construct
     * @return void
     */
    public function testInitializesInNewState(): void
    {
        $traceState = new TraceState();

        $this->assertTrue($traceState->isNew());
        $this->assertFalse($traceState->isActive());
        $this->assertFalse($traceState->isComplete());
    }

    /**
     * @covers ::setStateActive
     * @return void
     * @throws PerfbaseStateException
     */
    public function testTransitionsFromNewToActiveState(): void
    {
        $traceState = new TraceState();
        $traceState->setStateActive();

        $this->assertFalse($traceState->isNew());
        $this->assertTrue($traceState->isActive());
        $this->assertFalse($traceState->isComplete());
    }

    /**
     * @covers ::setStateComplete
     * @return void
     * @throws PerfbaseStateException
     */
    public function testTransitionsFromActiveToCompleteState(): void
    {
        $traceState = new TraceState();
        $traceState->setStateActive();
        $traceState->setStateComplete();

        $this->assertFalse($traceState->isNew());
        $this->assertFalse($traceState->isActive());
        $this->assertTrue($traceState->isComplete());
    }

    /**
     * @covers ::setStateComplete
     * @return void
     * @throws PerfbaseStateException
     */
    public function testThrowsExceptionIfTransitioningFromNewToCompleteDirectly(): void
    {
        $traceState = new TraceState();

        $this->expectException(PerfbaseStateException::class);
        $this->expectExceptionMessage('Invalid state transition from "new" to "complete". Required states: active.');

        $traceState->setStateComplete(); // This should throw
    }

    /**
     * @covers ::setStateActive
     * @return void
     * @throws PerfbaseStateException
     */
    public function testThrowsExceptionIfTransitioningFromCompleteToActive(): void
    {
        $traceState = new TraceState();
        $traceState->setStateActive();
        $traceState->setStateComplete();

        $this->expectException(PerfbaseStateException::class);
        $this->expectExceptionMessage('Invalid state transition from "complete" to "active". Required states: new.');

        $traceState->setStateActive(); // This should throw
    }
}
