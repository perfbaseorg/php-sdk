<?php

namespace Perfbase\SDK;

use Perfbase\SDK\Exception\PerfbaseExtensionException;
use Perfbase\SDK\Exception\PerfbaseInvalidConfigException;
use Perfbase\SDK\Exception\PerfbaseInvalidSpanException;
use Perfbase\SDK\Http\ApiClient;
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
    /**
     * The version of the Perfbase SDK
     */
    public const VERSION = '1.0.0';

    /**
     * The default span name used when starting a profiling session
     */
    private const DEFAULT_SPAN_NAME = 'default';

    /**
     * Flag to indicate if the Perfbase extension is available
     * -1 = not checked yet
     * 0 = not available
     * 1 = available
     * @var int
     */
    private static int $isAvailable = -1;

    /**
     * Manages the connection to the Perfbase API
     * @var ApiClient $apiClient
     */
    private ApiClient $apiClient;

    /**
     * The configuration object for the Perfbase SDK
     * @var Config $config
     */
    private Config $config;

    /**
     * The current state of the trace instance
     * @var array<string, bool>
     */
    private array $activeSpans = [];

    /**
     * Initialises the Perfbase SDK with the provided configuration
     * @throws PerfbaseExtensionException
     * @throws PerfbaseInvalidConfigException
     */
    public function __construct(Config $config)
    {
        // Check if the Perfbase extension is available
        $this->ensureIsAvailable();

        // Validate the configuration
        $config->validate();

        // Set the configuration
        $this->config = $config;

        // Create the API client
        $this->apiClient = new ApiClient($config);
    }

    /**
     * Ensures that the Perfbase extension is available
     *
     * @return void
     * @throws PerfbaseExtensionException
     */
    private function ensureIsAvailable(): void
    {
        if (!$this->isAvailable()) {
            throw new PerfbaseExtensionException('Perfbase extension is not available.');
        }
    }

    /**
     * Check if the extension is loaded and the required methods are available
     * Result is cached to avoid multiple checks.
     * @return bool
     */
    public function isAvailable(): bool
    {
        if (self::$isAvailable === -1) {
            self::$isAvailable = ExtensionUtils::perfbaseExtensionLoaded() && ExtensionUtils::perfbaseMethodsAvailable() ? 1 : 0;
        }
        return self::$isAvailable === 1;
    }

    /**
     * Starts the profiling session
     *
     * Enables the Perfbase profiler if the extension is installed.
     * This method should be called at the point where you want to begin
     * collecting performance data.
     * @param string $spanName The name of the span to start profiling
     * @throws PerfbaseExtensionException
     * @throws PerfbaseInvalidSpanException
     */
    public function startTraceSpan(string $spanName): void
    {
        $spanName = $this->validateSpanName($spanName);

        // Check to see if span is already active
        if (isset($this->activeSpans[$spanName])) {
            trigger_error(sprintf('Perfbase: Attempted to start span "%s" which is already active. ', $spanName), E_USER_WARNING);
            return;
        }

        // Set the state to active
        $this->activeSpans[$spanName] = true;
        perfbase_enable($spanName, $this->config->flags);
    }

    /**
     * Validates the span name to ensure it meets the required format
     *
     * @param string $spanName
     * @return string
     * @throws PerfbaseInvalidSpanException
     */
    private function validateSpanName(string $spanName): string
    {
        // Remove any leading or trailing whitespace
        $spanName = trim($spanName);

        // Check if the span name is empty, if so, use the default span name
        if (empty($spanName)) {
            $spanName = self::DEFAULT_SPAN_NAME;
        }

        // Check if the span name exceeds the maximum length
        if (strlen($spanName) > 64) {
            throw new PerfbaseInvalidSpanException('Span name exceeds maximum length of 64 characters.');
        }

        // Only allow alphanumeric characters, hyphens and underscores
        if (!preg_match('/^[a-zA-Z0-9-_]+$/', $spanName)) {
            throw new PerfbaseInvalidSpanException('Span name contains invalid characters. Only alphanumeric characters, hyphens and underscores are allowed.');
        }

        return $spanName;
    }

    /**
     * Stops the profiling session and sends collected data
     *
     * Disables the Perfbase profiler and retrieves the collected performance data.
     * The data is automatically sent to the Perfbase API for analysis.
     * @param string $spanName The name of the span to stop profiling, null for all.
     * @throws PerfbaseInvalidSpanException
     */
    public function stopTraceSpan(string $spanName): void
    {
        $spanName = $this->validateSpanName($spanName);

        // Check to see if span is active
        if (!$this->isSpanActive($spanName)) {
            trigger_error(sprintf('Perfbase: Attempted to stop span "%s" which is not active. ', $spanName), E_USER_WARNING);
            return;
        }

        // Set the state to complete
        perfbase_disable($spanName);
    }

    /**
     * Checks if the span is active
     *
     * @param string $spanName
     * @return bool
     */
    private function isSpanActive(string $spanName): bool
    {
        return isset($this->activeSpans[$spanName]);
    }

    /**
     * Sets the flags for the Perfbase profiler
     */
    public function setFlags(int $flags): void
    {
        $this->config->flags = $flags;
    }

    /**
     * Sends collected profiling data to the API
     * @return void
     */
    public function submitTrace(): void
    {
        $this->apiClient->submitTrace(
            base64_decode(perfbase_get_data())
        );
        $this->reset();
    }

    /**
     * Resets the trace session
     * @return void
     */
    public function reset()
    {
        perfbase_reset();
    }

    /**
     * Cleans up the trace session before destruction
     */
    public function __destruct()
    {
        $this->reset();
    }

}
