<?php

if (!function_exists('perfbase_enable')) {
    /**
     * Starts the Perfbase profiler
     *
     * @param string $spanName The name of the span to start profiling, defaults to "default"
     * @param int $flags Flags to enable specific profiling features
     * @return void
     */
    function perfbase_enable(string $spanName, int $flags)
    {
        // Stub only—no implementation needed
    }
}

if (!function_exists('perfbase_disable')) {
    /**
     * Stops the Perfbase profiler
     * @param string $spanName The name of the span to stop profiling
     * @return void
     */
    function perfbase_disable(string $spanName)
    {
        // Stub only—no implementation needed
    }
}

if (!function_exists('perfbase_set_attribute')) {
    /**
     * Sets an attribute for the Perfbase profiler
     *
     * @param string $key
     * @param string $value
     * @return void
     */
    function perfbase_set_attribute(string $key, string $value)
    {
        // Stub only—no implementation needed
    }
}


if (!function_exists('perfbase_reset')) {
    /**
     * Clears the collected profiling data and resets the profiler
     *
     * @return void
     */
    function perfbase_reset()
    {
        // Stub only—no implementation needed
    }
}

if (!function_exists('perfbase_get_data')) {
    /**
     * Retrieves the collected profiling data
     *
     * @return string
     */
    function perfbase_get_data(): string
    {
        // Stub only—no implementation needed
        return '';
    }
}
