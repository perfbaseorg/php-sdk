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
     * Retrieves the collected profiling data for a span
     * @param string $spanName The name of the span, or empty for all data
     * @return string
     */
    public function getSpanData(string $spanName = ''): string;

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