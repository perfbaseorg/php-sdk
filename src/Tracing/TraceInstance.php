<?php

namespace Perfbase\SDK\Tracing;

use JsonException;
use Perfbase\SDK\Config;
use Perfbase\SDK\Exception\PerfbaseEncodingException;
use Perfbase\SDK\Exception\PerfbaseInvalidConfigException;
use Perfbase\SDK\Exception\PerfbaseStateException;
use Perfbase\SDK\Http\ApiClient;
use Perfbase\SDK\Tracing\Compression\Compressor;

class TraceInstance
{
    /**
     * This is an array that can be used to store any additional metadata that
     * doesn't fit into the above fields.
     * @var array<string, scalar>
     */
    public array $metaData = [];

    /**
     * Manages the connection to the Perfbase API
     * @var ApiClient $apiClient
     */
    private ApiClient $apiClient;

    /**
     * The current state of the trace instance
     * @var TraceState
     */
    private TraceState $state;

    /**
     * Performance data collected during the profiling session
     * @var array<string, array<null|string|int>>
     */
    private array $performanceData;

    /**
     * The raised fields associated with the trace instance
     * @var Attributes
     */
    public Attributes $attributes;

    /**
     * @var Config $config
     */
    private Config $config;

    /**
     * @param Config $config
     * @throws PerfbaseInvalidConfigException
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
        $this->apiClient = new ApiClient($config);
        $this->state = new TraceState();
        $this->performanceData = [];
        $this->attributes = new Attributes();
    }

    /**
     * Starts the profiling session
     *
     * Enables the Perfbase profiler if the extension is installed.
     * This method should be called at the point where you want to begin
     * collecting performance data.
     * @throws PerfbaseStateException
     */
    public function startProfiling(): void
    {
        // Check if the state is new, otherwise we cannot start a new profiling session
        if ($this->state->isNew() === false) {
            throw new PerfbaseStateException('A profiling instance is already active and cannot be started again.');
        }

        // Enable the Perfbase profiler
        perfbase_enable(
            $this->config->getFlag(),
            $this->config->ignored_functions
        );

        // Set the state to active
        $this->state->setStateActive();
    }

    /**
     * Cleans up the trace instance before destruction
     * @throws PerfbaseStateException
     */
    public function __destruct()
    {
        if ($this->state->isActive()) {
            $this->stopProfiling();
        }
    }

    /**
     * Stops the profiling session and sends collected data
     *
     * Disables the Perfbase profiler and retrieves the collected performance data.
     * The data is automatically sent to the Perfbase API for analysis.
     * @param bool $andSend If true, send the collected data to the API; if false, do not send
     * @throws PerfbaseStateException
     * @throws JsonException
     */
    public function stopProfiling(bool $andSend = true): void
    {
        // State should be active, otherwise we cannot stop the profiling
        if ($this->state->isActive() === false) {
            throw new PerfbaseStateException('No profiling instance is active.');
        }

        // Set the state to complete
        perfbase_disable();

        // Retrieve the collected performance data
        // @phpstan-ignore-next-line
        $this->performanceData = perfbase_get_data();

        // Reset the extension, so we can start a new profiling session
        perfbase_clear();

        // Set the state to complete
        $this->state->setStateComplete();

        if ($andSend) {
            $this->sendProfilingData();
        }
    }

    /**
     * Sends collected profiling data to the API
     * @return void
     * @throws JsonException
     */
    public function sendProfilingData(): void
    {
        $this->apiClient->submitTrace(
            $this->transformData()
        );
    }

    /**
     * Transforms the collected data into a format that can be sent to the API
     * @return array<string, mixed>
     * @throws PerfbaseEncodingException
     */
    public function transformData(): array
    {
        // Compress the performance data and metadata
        $perfData = $this->encodeAndCompressData(
            $this->shrinkProfilingData($this->performanceData)
        );
        $metaData = count($this->metaData) ? $this->encodeAndCompressData($this->metaData) : null;

        // Return the transformed data
        return [
            'action' => $this->attributes->action,
            'user_id' => $this->attributes->userId,
            'user_ip' => $this->attributes->userIp,
            'user_agent' => $this->attributes->userAgent,
            'hostname' => $this->attributes->hostname,
            'environment' => $this->attributes->environment,
            'app_version' => $this->attributes->appVersion,
            'php_version' => $this->attributes->phpVersion,
            'http_method' => $this->attributes->httpMethod,
            'http_status_code' => $this->attributes->httpStatusCode,
            'http_url' => $this->attributes->httpUrl,
            'perf_data' => $perfData,
            'meta_data' => $metaData
        ];
    }

    /**
     * @param array<mixed> $data
     * @return string
     * @throws PerfbaseEncodingException
     */
    private function encodeAndCompressData(array $data): string
    {
        $json = json_encode($data);
        if ($json === false) {
            throw new PerfbaseEncodingException('Failed to encode data');
        }

        $compressed = gzencode($json, 6, FORCE_GZIP);
        if ($compressed === false) {
            throw new PerfbaseEncodingException('Failed to compress data');
        }
        return base64_encode($compressed);
    }

    /**
     * The idea behind this is that there's a VAST amount of repetition in the hierarchy data.
     * We can compress this by creating a map of all the unique keys and then replacing the keys
     * with their index in the map. This shrinks the performance data to (usually) 5-10% of
     * the original size.
     * @param array<string, mixed> $data
     * @return array<mixed>
     */
    private function shrinkProfilingData(array $data): array
    {
        // @phpstan-ignore-next-line
        return (new Compressor())->execute($data);
    }
}