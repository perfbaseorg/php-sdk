<?php

use Perfbase\SDK\Tracing\TraceState;
use Perfbase\SDK\Exception\PerfbaseStateException;

it('initializes in the new state', function () {
    $traceState = new TraceState();
    expect($traceState->isNew())->toBeTrue();
    expect($traceState->isActive())->toBeFalse();
    expect($traceState->isComplete())->toBeFalse();
});

it('transitions from new to active state', function () {
    $traceState = new TraceState();
    $traceState->setStateActive();
    expect($traceState->isNew())->toBeFalse();
    expect($traceState->isActive())->toBeTrue();
    expect($traceState->isComplete())->toBeFalse();
});

it('transitions from active to complete state', function () {
    $traceState = new TraceState();
    $traceState->setStateActive();
    $traceState->setStateComplete();
    expect($traceState->isNew())->toBeFalse();
    expect($traceState->isActive())->toBeFalse();
    expect($traceState->isComplete())->toBeTrue();
});

it('throws an exception if transitioning from new to complete directly', function () {
    $traceState = new TraceState();
    $traceState->setStateComplete(); // This should throw
})->throws(PerfbaseStateException::class, 'Invalid state transition from "new" to "complete". Required states: active.');

it('throws an exception if transitioning from complete to active', function () {
    $traceState = new TraceState();
    $traceState->setStateActive();
    $traceState->setStateComplete();
    $traceState->setStateActive(); // This should throw
})->throws(PerfbaseStateException::class, 'Invalid state transition from "complete" to "active". Required states: new.');
