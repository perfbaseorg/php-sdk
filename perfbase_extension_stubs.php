<?php

if (!function_exists('perfbase_enable')) {
    /**
     * Starts the Perfbase profiler
     *
     * @param int $flags
     * @param array<string> $ignored_functions
     * @return void
     */
    function perfbase_enable(int $flags, array $ignored_functions) {
        // Stub only—no implementation needed
    }
}

if (!function_exists('perfbase_disable')) {
    /**
     * Stops the Perfbase profiler
     *
     * @return void
     */
    function perfbase_disable() {
        // Stub only—no implementation needed
    }
}

if (!function_exists('perfbase_clear')) {
    /**
     * Clears the collected profiling data
     *
     * @return void
     */
    function perfbase_clear() {
        // Stub only—no implementation needed
    }
}

if (!function_exists('perfbase_get_data')) {
    /**
     * Retrieves the collected profiling data
     *
     * @return array
     */
    function perfbase_get_data(): array {
        // Stub only—no implementation needed
        return [];
    }
}
