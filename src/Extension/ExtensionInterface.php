<?php

namespace Perfbase\SDK\Extension;

interface ExtensionInterface
{
    /**
     * Check if the Perfbase extension is loaded and available
     * @return bool
     */
    public function isAvailable(): bool;

    /**
     * Starts a profiling span with attributes
     * @param string $spanName The name of the span to start profiling
     * @param int $flags Flags to enable specific profiling features
     * @param array<string, string> $attributes Initial attributes for the span
     * @return void
     */
    public function startSpan(string $spanName, int $flags, array $attributes = []): void;

    /**
     * Stops a profiling span
     * @param string $spanName The name of the span to stop profiling
     * @return void
     */
    public function stopSpan(string $spanName): void;

    /**
     * Retrieves the collected profiling data for the current trace.
     *
     * The current extension only supports whole-trace retrieval. The parameter
     * remains for backwards compatibility and should be left empty.
     *
     * @param string $spanName The span name. Must be empty with the current extension.
     * @deprecated The current extension only supports whole-trace retrieval.
     * @return string
     */
    public function getSpanData(string $spanName = ''): string;

    /**
     * Retrieves the encoding version for the current trace payload format.
     *
     * @return int
     */
    public function getVersion(): int;

    /**
     * Returns the bitwise OR of feature flags across every span held by
     * the profiler (active + draining + disabled-but-still-retained).
     * Used to annotate the outgoing trace so the UI can explain missing
     * columns (e.g. zero CPU times because `PERFBASE_FLAG_CPU_TIME`
     * wasn't enabled).
     *
     * @return int
     */
    public function getFlags(): int;

    /**
     * Sets an attribute for a specific span
     * @param string $key
     * @param string $value
     * @return void
     */
    public function setAttribute(string $key, string $value): void;

    /**
     * Clears all profiling data and resets the profiler
     * @return void
     */
    public function reset(): void;
}
