<?php

namespace Perfbase\SDK;

use Perfbase\SDK\Exception\PerfbaseExtensionException;
use Perfbase\SDK\Exception\PerfbaseInvalidConfigException;
use Perfbase\SDK\Exception\PerfbaseInvalidSpanException;
use Perfbase\SDK\Extension\ExtensionInterface;
use Perfbase\SDK\Extension\PerfbaseExtension;
use Perfbase\SDK\Http\ApiClient;

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
     * The extension interface for profiling operations
     * @var ExtensionInterface
     */
    private ExtensionInterface $extension;

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
     * @param Config $config
     * @param ExtensionInterface|null $extension
     * @param ApiClient|null $apiClient
     * @throws PerfbaseExtensionException
     * @throws PerfbaseInvalidConfigException
     */
    public function __construct(Config $config, ?ExtensionInterface $extension = null, ?ApiClient $apiClient = null)
    {
        // Use provided extension or create default
        $this->extension = $extension ?? new PerfbaseExtension();
        
        // Check if the Perfbase extension is available
        $this->ensureIsAvailable();

        // Validate the configuration
        $config->validate();

        // Set the configuration
        $this->config = $config;

        // Create or use provided API client
        $this->apiClient = $apiClient ?? new ApiClient($config);
    }

    /**
     * Ensures that the Perfbase extension is available
     *
     * @return void
     * @throws PerfbaseExtensionException
     */
    private function ensureIsAvailable(): void
    {
        if (!$this->extension->isAvailable()) {
            throw new PerfbaseExtensionException('Perfbase extension is not available.');
        }
    }

    /**
     * Starts the profiling session
     *
     * Enables the Perfbase profiler if the extension is installed.
     * This method should be called at the point where you want to begin
     * collecting performance data.
     * @param string $spanName The name of the span to start profiling
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
        $this->extension->enable($spanName, $this->config->flags);
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
     * @param string $spanName The name of the span to stop profiling
     * @return bool Will equal true if the span was successfully stopped, false if it was not active.
     * @throws PerfbaseInvalidSpanException
     */
    public function stopTraceSpan(string $spanName): bool
    {
        $spanName = $this->validateSpanName($spanName);

        // Check to see if span is active
        if (!$this->isSpanActive($spanName)) {
            return false;
        }

        // Set the state to complete
        unset($this->activeSpans[$spanName]);
        $this->extension->disable($spanName);

        return true;
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
            $this->getTraceData()
        );
        $this->reset();
    }

    /**
     * Retrieves the trace data collected during the profiling session
     * @return string
     */
    public function getTraceData(): string
    {
        return $this->extension->getData();
    }

    /**
     * Resets the trace session
     * @return void
     */
    public function reset()
    {
        $this->activeSpans = [];
        $this->extension->reset();
    }

    /**
     * Cleans up the trace session before destruction
     */
    public function __destruct()
    {
        $this->reset();
    }

    /**
     * Check if the extension is available
     * @return bool
     */
    public function isExtensionAvailable(): bool
    {
        return $this->extension->isAvailable();
    }

}
