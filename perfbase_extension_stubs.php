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

if (!function_exists('perfbase_version')) {
    /**
     * Returns the extension release version as a semver string
     * (e.g. `0.1.0`, or `0.1.<build_id>` in CI builds).
     *
     * @return string
     */
    function perfbase_version(): string
    {
        // Stub only—no implementation needed
        return '0.0.0';
    }
}

if (!function_exists('perfbase_wire_version')) {
    /**
     * Returns the wire/encoding format version of the bytes produced by
     * `perfbase_get_data()`. Bumps when the on-the-wire format changes in
     * a way consumers need to branch on.
     *
     * @return int
     */
    function perfbase_wire_version(): int
    {
        // Stub only—no implementation needed
        return 1;
    }
}

if (!function_exists('perfbase_get_flags')) {
    /**
     * Returns the bitwise OR of feature flags across every span held by
     * the profiler (active + draining + disabled-but-still-retained).
     * Returns 0 when no spans have been enabled since the last reset.
     *
     * @return int
     */
    function perfbase_get_flags(): int
    {
        // Stub only—no implementation needed
        return 0;
    }
}
