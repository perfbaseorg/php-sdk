<?php

namespace Perfbase\SDK;

use Perfbase\SDK\Exception\PerfbaseApiKeyMissingException;
use Perfbase\SDK\Exception\PerfbaseStateException;
use Perfbase\SDK\Tracing\TraceInstance;
use Perfbase\SDK\Utils\ExtensionUtils;

/**
 * Main client class for the Perfbase SDK
 *
 * This class provides the primary interface for interacting with the Perfbase
 * profiling system. It handles starting and stopping profiling sessions and
 * sending the collected data to the Perfbase API.
 *
 * @package Perfbase\SDK
 */
class Perfbase
{
    private static int $isAvailable = -1;

    /**
     * Holds the current instance of profiling data
     * @var TraceInstance|null $instance
     */
    private static ?TraceInstance $instance = null;

    /**
     * Check if the extension is loaded and the required methods are available
     * Result is cached to avoid multiple checks.
     * @return bool
     */
    public static function isAvailable(): bool
    {
        if (self::$isAvailable === -1) {
            self::$isAvailable = ExtensionUtils::perfbaseExtensionLoaded() && ExtensionUtils::perfbaseMethodsAvailable() ? 1 : 0;
        }
        return self::$isAvailable === 1;
    }

    /**
     * Check if we have an available instance.
     * @return bool
     */
    public static function hasInstance(): bool
    {
        return self::$instance !== null;
    }

    /**
     * Create a new trace instance.
     * @param Config $config
     * @return TraceInstance
     * @throws PerfbaseStateException
     * @throws PerfbaseApiKeyMissingException
     */
    public static function createInstance(Config $config): TraceInstance
    {
        if (self::$instance !== null) {
            throw new PerfbaseStateException();
        }
        return self::$instance = new TraceInstance($config);
    }

    /**
     * Get the current trace instance, if available.
     * @return TraceInstance
     * @throws PerfbaseStateException
     */
    public static function instance(): TraceInstance
    {
        if (self::$instance === null) {
            throw new PerfbaseStateException();
        }
        return self::$instance;
    }

    /**
     * Wipes the current instance, if available.
     * @return void
     */
    public static function resetInstance(): void
    {
        self::$instance = null;
    }

}
