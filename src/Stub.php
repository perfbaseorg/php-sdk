<?php

/**
 * Enable Perfbase profiling
 *
 * @return void
 */
function perfbase_enable(): void {}

/**
 * Disable Perfbase profiling and return collected data
 *
 * @return array{
 *   timestamp: int,
 *   duration: float,
 *   memory_peak: int,
 *   traces: array<int, array{
 *     file: string,
 *     line: int,
 *     function: string,
 *     time: float,
 *     memory: int
 *   }>
 * }
 */
function perfbase_disable(): array {}
