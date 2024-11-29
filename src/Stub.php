<?php

/**
 * Enable Perfbase profiling
 *
 * This function is a stub that represents the actual function provided by the
 * Perfbase extension. When the extension is installed, this stub will be replaced
 * with the actual implementation.
 *
 * @return void
 */
function perfbase_enable(): void {}

/**
 * Disable Perfbase profiling and return collected data
 *
 * This function is a stub that represents the actual function provided by the
 * Perfbase extension. When the extension is installed, this stub will be replaced
 * with the actual implementation.
 *
 * @return array<string, array{
 *   0: int,    // ds - Code definition start line number
 *   1: int,    // de - Code definition end line number
 *   2: string, // df - Code definition file name
 *   3: int,    // ct - Calls count
 *   4: int,    // wt - Wall time in microseconds
 *   5: int,    // mu - Memory usage in bytes
 *   6: int     // pmu - Peak memory usage in bytes
 * }> Keys are "caller->callee" function pairs (e.g. "main()~example_function()")
 */
function perfbase_disable(): array
{
    return [
        'main()~example_function()' => [0, 0, '', 1, 0, 0, 0]
    ];
}