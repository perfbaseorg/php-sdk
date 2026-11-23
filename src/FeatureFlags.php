<?php

namespace Perfbase\SDK;

/**
 * Feature flags for controlling which profiling features are enabled.
 *
 * Bit positions mirror the extension's `PERFBASE_FLAG_*` constants.
 *
 * Semantics (applied by the extension):
 * - `0` or `AllFlags`: every feature except the opt-in flags
 *   (`UsePreciseClock` and `TrackCpuTime`)
 * - A mask containing only opt-in flags: that default set plus those flags
 *   (e.g. `TrackCpuTime` alone = everything in the default set + CPU time)
 * - Any mask containing a regular flag: exactly the named features
 * - `DefaultFlags`: SDK-curated exact mask (see below)
 *
 * Wall time is always tracked; there is no flag for it.
 */
class FeatureFlags
{
    /**
     * The extension treats `0` as "every feature except the opt-in flags".
     */
    public const AllFlags = 0;

    /**
     * SDK-curated defaults.
     *
     * Every non-deprecated feature except per-function CPU time and memory
     * allocation tracking, which add per-call overhead that many integrations
     * do not need on the happy path. Includes `UsePreciseClock` so sub-tick
     * timings are not quantised to zero on hosts without a usable TSC.
     */
    public const DefaultFlags =
        self::UsePreciseClock |
        self::TrackExceptions |
        self::TrackFileCompilation |
        self::TrackPdo |
        self::TrackMongodb |
        self::TrackElasticsearch |
        self::TrackCaches |
        self::TrackHttp |
        self::TrackMail |
        self::TrackFileOperations |
        self::TrackProc |
        self::TrackProcessList |
        self::TrackErrors |
        self::TrackMagicMethods |
        self::TrackOpcache |
        self::TrackStreams;

    /**
     * Bits reserved by the extension. They belonged to flags that never had
     * any effect; the extension ignores them. Kept so existing configuration
     * that still sets them keeps validating.
     */
    public const DeprecatedFlagsMask =
        self::TrackWallTime |
        self::TrackArguments |
        self::TrackFileDefinitions |
        self::TrackSessions |
        self::TrackSerialization |
        self::TrackRegex;

    /**
     * Bitmask of every accepted flag for validation and documentation helpers.
     */
    public const ValidFlagsMask =
        self::UsePreciseClock |
        self::TrackCpuTime |
        self::TrackMemoryAllocation |
        self::TrackExceptions |
        self::TrackFileCompilation |
        self::TrackPdo |
        self::TrackMongodb |
        self::TrackElasticsearch |
        self::TrackCaches |
        self::TrackHttp |
        self::TrackMail |
        self::TrackFileOperations |
        self::TrackProc |
        self::TrackProcessList |
        self::TrackErrors |
        self::TrackMagicMethods |
        self::TrackOpcache |
        self::TrackStreams |
        self::DeprecatedFlagsMask;

    /**
     * Opt-in. Guarantee high-resolution wall timing. Where the calibrated TSC
     * (Linux x86-64), vDSO `CLOCK_MONOTONIC` (Linux arm64) or
     * `mach_absolute_time` (macOS) is already the default this changes
     * nothing; elsewhere it replaces the coarse kernel-tick clock.
     */
    public const UsePreciseClock = 1 << 0;

    /**
     * @deprecated Reserved by the extension and ignored. Wall time is always tracked.
     */
    public const TrackWallTime = 1 << 1;

    /**
     * Opt-in. Per-function CPU time via `clock_gettime(CLOCK_THREAD_CPUTIME_ID)`.
     * A real syscall on Linux, paid twice per profiled call.
     */
    public const TrackCpuTime = 1 << 2;

    /**
     * Track memory allocation and deallocation per function (Zend MM).
     */
    public const TrackMemoryAllocation = 1 << 3;

    /**
     * @deprecated Reserved by the extension and ignored; it never had any effect.
     */
    public const TrackArguments = 1 << 4;

    /**
     * Count and sample exception throws (PHP 8.0+).
     */
    public const TrackExceptions = 1 << 5;

    /**
     * Count and time file compilation (`include` / `require`; OPcache misses only when OPcache is active).
     */
    public const TrackFileCompilation = 1 << 6;

    /**
     * @deprecated Reserved by the extension and ignored; it never had any effect.
     */
    public const TrackFileDefinitions = 1 << 7;

    /**
     * @deprecated Reserved by the extension and ignored; it never had any effect.
     */
    public const TrackSessions = 1 << 8;

    /**
     * @deprecated Reserved by the extension and ignored; it never had any effect.
     */
    public const TrackSerialization = 1 << 9;

    /**
     * @deprecated Reserved by the extension and ignored; it never had any effect.
     */
    public const TrackRegex = 1 << 10;

    /**
     * Track PDO, mysqli and legacy mysql queries.
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
     * Track Redis and Memcached calls.
     */
    public const TrackCaches = 1 << 14;

    /**
     * Track cURL, Guzzle and AWS SDK calls.
     */
    public const TrackHttp = 1 << 15;

    /**
     * Track mail() calls.
     */
    public const TrackMail = 1 << 16;

    /**
     * Track fopen, fread, fwrite, and related file I/O.
     */
    public const TrackFileOperations = 1 << 17;

    /**
     * Track exec / shell_exec / proc_open / popen and the sleep family.
     */
    public const TrackProc = 1 << 18;

    /**
     * Capture the top-N running processes at trace end.
     */
    public const TrackProcessList = 1 << 19;

    /**
     * Count and sample PHP errors, warnings, and notices (PHP 8.0+).
     */
    public const TrackErrors = 1 << 20;

    /**
     * Count magic method invocations such as __call, __get, and __set.
     */
    public const TrackMagicMethods = 1 << 21;

    /**
     * Capture OPcache statistics (hits/misses delta over the trace, cached-script
     * count, memory used/free/wasted, interned strings, hit rate). No-op when
     * OPcache isn't loaded or `opcache.restrict_api` is set.
     */
    public const TrackOpcache = 1 << 22;

    /**
     * PHP 8.6+: stream errors as runtime events and `Io\Poll\Context::wait`
     * metadata. Accepted but inert on older PHP versions.
     */
    public const TrackStreams = 1 << 23;
}
