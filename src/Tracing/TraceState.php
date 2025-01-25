<?php

namespace Perfbase\SDK\Tracing;

use Perfbase\SDK\Exception\PerfbaseStateException;

class TraceState
{

    private const PERFBASE_STATE_NEW = 'new';
    private const PERFBASE_STATE_ACTIVE = 'active';
    private const PERFBASE_STATE_COMPLETE = 'complete';

    private string $state;

    public function __construct()
    {
        $this->state = self::PERFBASE_STATE_NEW;
    }

    public function isNew(): bool
    {
        return $this->state === self::PERFBASE_STATE_NEW;
    }

    public function isActive(): bool
    {
        return $this->state === self::PERFBASE_STATE_ACTIVE;
    }

    public function isComplete(): bool
    {
        return $this->state === self::PERFBASE_STATE_COMPLETE;
    }

    /**
     * @throws PerfbaseStateException
     */
    public function setStateActive(): void
    {
        $this->setState(self::PERFBASE_STATE_ACTIVE, [self::PERFBASE_STATE_NEW]);
    }

    /**
     * @param string $requested The state to transition to.
     * @param array<string> $required The states that are permitted to transition from.
     * @throws PerfbaseStateException
     */
    private function setState(string $requested, array $required): void
    {
        if (!in_array($this->state, $required, true)) {
            throw new PerfbaseStateException(sprintf(
                'Invalid state transition from "%s" to "%s". Required states: %s.',
                $this->state,
                $requested,
                implode(', ', $required)
            ));
        }
        $this->state = $requested;
    }

    /**
     * @throws PerfbaseStateException
     */
    public function setStateComplete(): void
    {
        $this->setState(self::PERFBASE_STATE_COMPLETE, [self::PERFBASE_STATE_ACTIVE]);
    }

}