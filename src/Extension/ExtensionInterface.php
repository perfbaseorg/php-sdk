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
     * Starts the Perfbase profiler
     * @param string $spanName The name of the span to start profiling
     * @param int $flags Flags to enable specific profiling features
     * @return void
     */
    public function enable(string $spanName, int $flags): void;

    /**
     * Stops the Perfbase profiler
     * @param string $spanName The name of the span to stop profiling
     * @return void
     */
    public function disable(string $spanName): void;

    /**
     * Retrieves the collected profiling data
     * @return string
     */
    public function getData(): string;

    /**
     * Clears the collected profiling data and resets the profiler
     * @return void
     */
    public function reset(): void;

    /**
     * Sets an attribute for the Perfbase profiler
     * @param string $key
     * @param string $value
     * @return void
     */
    public function setAttribute(string $key, string $value): void;
}