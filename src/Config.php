<?php

namespace Perfbase\SDK;

/**
 * Configuration class for the Perfbase SDK
 *
 * This class manages all configuration settings required for the SDK to function,
 * including API credentials, endpoints, and operational parameters.
 *
 * @package Perfbase\SDK
 */
class Config
{
    /**
     * The API key to use for authenticating with the Perfbase API
     * @var string|null
     */
    public ?string $apiKey = null;

    /**
     * Base URL for the Perfbase API
     * @var string
     */
    public string $apiUrl = 'https://receiver.perfbase.com';

    /**
     * Functions to ignore during profiling
     * @var array<string>
     */
    public array $ignored_functions = [];

    /**
     * Utilises a faster but less accurate clock for profiling.
     * Pros - Faster to call, less overhead, less impact on the profiled application.
     * Cons - Lower resolution, less accurate, may not be suitable for all use cases.
     * Notes: You might start seeing things like 0ms timings for some operations.
     * @var bool
     */
    public bool $use_coarse_clock = false;

    /**
     * Enables tracking of exceptions during profiling.
     * @todo This functionality is currently disabled
     * @var bool
     */
    public bool $track_exceptions = false;

    /**
     * Enables tracking of file compilation during profiling.
     * Measures the time taken to compile PHP files before execution.
     * @var bool
     */
    public bool $track_file_compilation = true;

    /**
     * Enables tracking of memory allocation during profiling.
     * Taps into the Zend Memory Manager to track memory allocation and deallocation.
     * @var bool
     */
    public bool $track_memory_allocation = false;

    /**
     * Enables tracking of CPU time during profiling.
     * @var bool
     */
    public bool $track_cpu_time = true;

    /**
     * Tracks the names + locations of files that are profiled.
     * @var bool
     */
    public bool $track_file_definitions = false;

    /**
     * Tracks PDO database operations during profiling.
     * This includes queries + execution times, but not results.
     * @var bool
     */
    public bool $track_pdo = true;

    /**
     * Tracks HTTP requests during profiling.
     * Exceptions to this are:
     * A. If you utilise fsockopen for raw socket connections.
     * B. If you utilise file_get_contents.
     * C. If you utilise system/exec/shell_exec for curl requests.
     * @var bool
     */
    public bool $track_http = true;

    /**
     * Tracks calls to caching mechanisms during profiling.
     * This includes Redis, Memcached, APC, and other caching mechanisms.
     * @var bool
     */
    public bool $track_caches = true;

    /**
     * Tracks MongoDB operations during profiling.
     * @var bool
     */
    public bool $track_mongodb = true;

    /**
     * Tracks Elasticsearch operations during profiling.
     * @var bool
     */
    public bool $track_elasticsearch = true;

    /**
     * Tracks queue operations during profiling.
     * @var bool
     */
    public bool $track_queues = true;

    /**
     * Tracks AWS SDK operations during profiling.
     * @var bool
     */
    public bool $track_aws_sdk = true;

    /**
     * Tracks file operations during profiling.
     * @var bool
     */
    public bool $track_file_operations = true;

    /**
     * Proxy server to use for connecting to the Perfbase API
     * Format: [scheme]://[user]:[password]@[host]:[port]
     * Eg: http://username:password@proxy.example.com:8080
     * @var string|null
     */
    public ?string $proxy = null;

    /**
     * Timeout for API requests in seconds
     * Default: 10 seconds
     * @var int
     */
    public int $timeout = 10;


    /**
     * @param string|null $apiKey
     * @param string|null $apiUrl
     * @param array<string>|null $ignored_functions
     * @param bool|null $use_coarse_clock
     * @param bool|null $track_exceptions
     * @param bool|null $track_file_compilation
     * @param bool|null $track_memory_allocation
     * @param bool|null $track_cpu_time
     * @param bool|null $track_file_definitions
     * @param bool|null $track_pdo
     * @param bool|null $track_http
     * @param bool|null $track_caches
     * @param bool|null $track_mongodb
     * @param bool|null $track_elasticsearch
     * @param bool|null $track_queues
     * @param bool|null $track_aws_sdk
     * @param bool|null $track_file_operations
     * @param string|null $proxy
     */
    public function __construct(
        ?string $apiKey = null,
        ?string $apiUrl = null,
        ?array  $ignored_functions = null,
        ?bool   $use_coarse_clock = null,
        ?bool   $track_exceptions = null,
        ?bool   $track_file_compilation = null,
        ?bool   $track_memory_allocation = null,
        ?bool   $track_cpu_time = null,
        ?bool   $track_file_definitions = null,
        ?bool   $track_pdo = null,
        ?bool   $track_http = null,
        ?bool   $track_caches = null,
        ?bool   $track_mongodb = null,
        ?bool   $track_elasticsearch = null,
        ?bool   $track_queues = null,
        ?bool   $track_aws_sdk = null,
        ?bool   $track_file_operations = null,
        ?string $proxy = null
    )
    {
        // Get all the properties of this class
        $properties = array_keys(get_object_vars($this));

        // Get all arguments passed to the constructor
        $args = func_get_args();

        // Dynamically map arguments to class properties
        foreach ($properties as $index => $property) {
            if (isset($args[$index])) {
                $this->$property = $args[$index];
            }
        }
    }

    /**
     * Create a new Config instance from an array of configuration options
     * @param array<string, mixed> $config
     * @return self
     */
    public static function fromArray(array $config): self
    {
        $instance = new self();

        foreach ($config as $key => $value) {
            $property = lcfirst(str_replace('_', '', ucwords($key, '_')));
            if (property_exists($instance, $property)) {
                $instance->$property = $value;
            }
        }

        return $instance;
    }


    /**
     * Enumerates the flags to enable specific profiling features
     * @return int
     */
    public function getFlag(): int
    {
        $flag = 0;

        if ($this->use_coarse_clock) {
            $flag |= 1;
        }
        if ($this->track_exceptions) {
            $flag |= 2;
        }
        if ($this->track_file_compilation) {
            $flag |= 4;
        }
        if ($this->track_memory_allocation) {
            $flag |= 8;
        }
        if ($this->track_cpu_time) {
            $flag |= 32;
        }
        if ($this->track_file_definitions) {
            $flag |= 64;
        }
        if ($this->track_pdo) {
            $flag |= 128;
        }
        if ($this->track_http) {
            $flag |= 256;
        }
        if ($this->track_caches) {
            $flag |= 512;
        }
        if ($this->track_mongodb) {
            $flag |= 1024;
        }
        if ($this->track_elasticsearch) {
            $flag |= 2048;
        }
        if ($this->track_queues) {
            $flag |= 4096;
        }
        if ($this->track_aws_sdk) {
            $flag |= 8192;
        }
        if ($this->track_file_operations) {
            $flag |= 16384;
        }

        return $flag;
    }
}