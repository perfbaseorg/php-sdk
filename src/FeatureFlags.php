<?php

namespace Perfbase\SDK;

/**
 * FeatureFlags
 */
class FeatureFlags
{

    /**
     * Default flags to be used for profiling.
     * This includes all the flags that are enabled by default.
     */
    public const DefaultFlags =
        self::UseCoarseClock |
        self::TrackCpuTime |
        self::TrackPdo |
        self::TrackHttp |
        self::TrackCaches |
        self::TrackMongodb |
        self::TrackElasticsearch |
        self::TrackQueues |
        self::TrackAwsSdk;

    /**
     * All flags.
     */
    public const AllFlags =
        self::UseCoarseClock |
        self::TrackExceptions |
        self::TrackFileCompilation |
        self::TrackMemoryAllocation |
        self::TrackCpuTime |
        self::TrackFileDefinitions |
        self::TrackPdo |
        self::TrackHttp |
        self::TrackCaches |
        self::TrackMongodb |
        self::TrackElasticsearch |
        self::TrackQueues |
        self::TrackAwsSdk |
        self::TrackFileOperations;

    /**
     * Utilises a faster but less accurate clock for profiling.
     * Pros - Faster to call, less overhead, less impact on the profiled application.
     * Cons - Lower resolution, less accurate, may not be suitable for all use cases.
     * Notes: You might start seeing things like 0ms timings for some operations.
     */
    public const UseCoarseClock = 1 << 0;

    /**
     * Enables tracking of exceptions during profiling.
     * @todo This functionality is currently disabled
     */
    public const TrackExceptions = 1 << 1;

    /**
     * Enables tracking of file compilation during profiling.
     * Measures the time taken to compile PHP files before execution.
     */
    public const TrackFileCompilation = 1 << 2;

    /**
     * Enables tracking of memory allocation during profiling.
     * Taps into the Zend Memory Manager to track memory allocation and deallocation.
     */
    public const TrackMemoryAllocation = 1 << 3;

    /**
     * Enables tracking of CPU time during profiling.
     */
    public const TrackCpuTime = 1 << 4;

    /**
     * Tracks the names + locations of files that are profiled.
     */
    public const TrackFileDefinitions = 1 << 5;

    /**
     * TTracks PDO database operations during profiling.
     *  This includes queries + execution times, but not results.
     */
    public const TrackPdo = 1 << 6;

    /**
     * Tracks HTTP requests during profiling.
     * Exceptions to this are:
     * A. If you utilise fsockopen for raw socket connections.
     * B. If you utilise file_get_contents.
     * C. If you utilise system/exec/shell_exec for curl requests.
     */
    public const TrackHttp = 1 << 7;

    /**
     * Tracks calls to caching mechanisms during profiling.
     * This includes Redis, Memcached, APC, and other caching mechanisms.
     */
    public const TrackCaches = 1 << 8;

    /**
     * Tracks MongoDB operations during profiling.
     */
    public const TrackMongodb = 1 << 9;

    /**
     * Tracks Elasticsearch operations during profiling.
     */
    public const TrackElasticsearch = 1 << 10;

    /**
     * Tracks queue operations during profiling.
     */
    public const TrackQueues = 1 << 11;

    /**
     * Tracks AWS SDK operations during profiling.
     */
    public const TrackAwsSdk = 1 << 12;

    /**
     * Tracks file operations during profiling.
     */
    public const TrackFileOperations = 1 << 13;

}