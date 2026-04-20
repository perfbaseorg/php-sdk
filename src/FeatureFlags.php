<?php

namespace Perfbase\SDK;

/**
 * Feature flags for controlling which profiling features are enabled.
 *
 * Semantics:
 * - `0` or `AllFlags`: enable every feature except `UsePreciseClock`
 * - `DefaultFlags`: SDK-curated defaults that exclude niche high-overhead flags
 * - `X`: enable only feature `X`
 * - `X | Y`: enable only features `X` and `Y`
 */
class FeatureFlags
{
    /**
     * Rust treats `0` as the "all default features" sentinel.
     */
    public const AllFlags = 0;

    /**
     * SDK-curated defaults.
     *
     * This excludes CPU-time and memory-allocation tracking by default because
     * they are more specialized and can add overhead that many integrations do
     * not need on the happy path.
     */
    public const DefaultFlags = 
        self::UsePreciseClock |
        self::TrackWallTime |
        self::TrackArguments |
        self::TrackExceptions |
        self::TrackFileCompilation |
        self::TrackFileDefinitions |
        self::TrackSessions |
        self::TrackSerialization |
        self::TrackRegex |
        self::TrackPdo |
        self::TrackMongodb |
        self::TrackElasticsearch |
        self::TrackCaches |
        self::TrackHttp |
        self::TrackMail |
        self::TrackFileOperations |
        self::TrackProc |
        self::TrackProcessList;

    /**
     * Bitmask of every known flag for validation and documentation helpers.
     */
    public const ValidFlagsMask =
        self::UsePreciseClock |
        self::TrackWallTime |
        self::TrackCpuTime |
        self::TrackMemoryAllocation |
        self::TrackArguments |
        self::TrackExceptions |
        self::TrackFileCompilation |
        self::TrackFileDefinitions |
        self::TrackSessions |
        self::TrackSerialization |
        self::TrackRegex |
        self::TrackPdo |
        self::TrackMongodb |
        self::TrackElasticsearch |
        self::TrackCaches |
        self::TrackHttp |
        self::TrackMail |
        self::TrackFileOperations |
        self::TrackProc |
        self::TrackProcessList;

    /**
     * Opt in to the high-resolution monotonic clock.
     */
    public const UsePreciseClock = 1 << 0;

    /**
     * Track wall clock time.
     */
    public const TrackWallTime = 1 << 1;

    /**
     * Track CPU cycles via getrusage().
     */
    public const TrackCpuTime = 1 << 2;

    /**
     * Track memory allocation and deallocation.
     */
    public const TrackMemoryAllocation = 1 << 3;

    /**
     * Capture function arguments.
     */
    public const TrackArguments = 1 << 4;

    /**
     * Track exception throws.
     */
    public const TrackExceptions = 1 << 5;

    /**
     * Track file include and require operations.
     */
    public const TrackFileCompilation = 1 << 6;

    /**
     * Track class and function definitions.
     */
    public const TrackFileDefinitions = 1 << 7;

    /**
     * Track session operations.
     */
    public const TrackSessions = 1 << 8;

    /**
     * Track serialize, json_encode, and similar operations.
     */
    public const TrackSerialization = 1 << 9;

    /**
     * Track preg_* operations.
     */
    public const TrackRegex = 1 << 10;

    /**
     * Track PDO and mysqli queries.
     */
    public const TrackPdo = 1 << 11;

    /**
     * Track MongoDB operations.
     */
    public const TrackMongodb = 1 << 12;

    /**
     * Track Elasticsearch operations.
     */
    public const TrackElasticsearch = 1 << 13;

    /**
     * Track Redis, Memcached, and other cache calls.
     */
    public const TrackCaches = 1 << 14;

    /**
     * Track cURL and HTTP calls.
     */
    public const TrackHttp = 1 << 15;

    /**
     * Track mail and SMTP operations.
     */
    public const TrackMail = 1 << 16;

    /**
     * Track fopen, fread, fwrite, and related file I/O.
     */
    public const TrackFileOperations = 1 << 17;

    /**
     * Track proc_open, exec, and shell_exec.
     */
    public const TrackProc = 1 << 18;

    /**
     * Capture the top-N running processes at trace end.
     */
    public const TrackProcessList = 1 << 19;
}
