<?php

namespace Perfbase\SDK;

use Perfbase\SDK\Exception\PerfbaseExtensionException;
use Perfbase\SDK\Exception\PerfbaseException;
use Perfbase\SDK\Exception\PerfbaseInvalidConfigException;
use Perfbase\SDK\Exception\PerfbaseInvalidSpanException;
use Perfbase\SDK\Extension\ExtensionInterface;
use Perfbase\SDK\Extension\PerfbaseExtension;
use Perfbase\SDK\Http\ApiClient;
use Perfbase\SDK\SubmitResult;

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
    public const VERSION = '1.1.0';

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
     * Currently active span names
     * @var array<string>
     */
    private array $activeSpanNames = [];

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
     * Starts a profiling span with optional attributes
     *
     * @param string $spanName The name of the span to start profiling
     * @param array<string, string> $attributes Initial attributes for the span
     * @throws PerfbaseInvalidSpanException
     */
    public function startTraceSpan(string $spanName, array $attributes = []): void
    {
        $spanName = trim($spanName) ?: self::DEFAULT_SPAN_NAME;

        // Check to see if span is already active
        if (in_array($spanName, $this->activeSpanNames)) {
            trigger_error(sprintf('Perfbase: Attempted to start span "%s" which is already active. ', $spanName), E_USER_WARNING);
            return;
        }

        // Set the state to active
        $this->activeSpanNames[] = $spanName;
        $this->extension->startSpan($spanName, $this->config->flags, $attributes);
    }

    /**
     * Stops the profiling session
     *
     * @param string $spanName The name of the span to stop profiling
     * @return bool Will equal true if the span was successfully stopped, false if it was not active.
     */
    public function stopTraceSpan(string $spanName): bool
    {
        $spanName = trim($spanName) ?: self::DEFAULT_SPAN_NAME;

        // Check to see if span is active
        if (!$this->isSpanActive($spanName)) {
            return false;
        }

        // Remove from active spans
        $this->activeSpanNames = array_values(array_filter($this->activeSpanNames, fn($name) => $name !== $spanName));
        $this->extension->stopSpan($spanName);

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
        return in_array($spanName, $this->activeSpanNames);
    }

    /**
     * Sets the flags for the Perfbase profiler
     */
    public function setFlags(int $flags): void
    {
        $this->config->flags = $flags;
    }

    /**
     * Sends collected profiling data to the API.
     *
     * Resets trace state on success. On failure, trace state is preserved
     * so the caller can decide whether to retry or discard.
     *
     * @return SubmitResult
     */
    public function submitTrace(): SubmitResult
    {
        $traceData = $this->getTraceData();

        if ($traceData === '') {
            throw new PerfbaseException('Extension returned empty trace data');
        }

        $payloadVersion = $this->extension->getVersion();
        if ($payloadVersion <= 0) {
            throw new PerfbaseException('Extension returned invalid encoding version');
        }

        $result = $this->apiClient->submitTrace(
            $traceData,
            $payloadVersion,
            gmdate('Y-m-d\TH:i:s\Z')
        );

        if ($result->isSuccess()) {
            $this->reset();
        }

        return $result;
    }

    /**
     * Retrieves the trace data collected during the profiling session
     * @param string $spanName Optional span name to get data for specific span
     * @return string
     */
    public function getTraceData(string $spanName = ''): string
    {
        return $this->extension->getSpanData($spanName);
    }

    /**
     * Resets the trace session
     * @return void
     */
    public function reset()
    {
        $this->activeSpanNames = [];
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

    /**
     * Static method to check if Perfbase is available
     * This is for backward compatibility with older versions
     * @return bool
     */
    public static function isAvailable(): bool
    {
        try {
            $extension = new PerfbaseExtension();
            return $extension->isAvailable();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Sets an attribute for the current trace
     * @param string $key
     * @param string $value
     * @return void
     */
    public function setAttribute(string $key, string $value): void
    {
        $this->extension->setAttribute($key, $value);
    }

}
