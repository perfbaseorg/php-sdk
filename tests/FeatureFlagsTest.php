<?php

namespace Perfbase\SDK\Tests;

use Perfbase\SDK\FeatureFlags;

/**
 * @coversDefaultClass \Perfbase\SDK\FeatureFlags
 */
class FeatureFlagsTest extends BaseTest
{
    /**
     * @return list<int>
     */
    private function individualFlags(): array
    {
        return [
            FeatureFlags::UsePreciseClock,
            FeatureFlags::TrackWallTime,
            FeatureFlags::TrackCpuTime,
            FeatureFlags::TrackMemoryAllocation,
            FeatureFlags::TrackArguments,
            FeatureFlags::TrackExceptions,
            FeatureFlags::TrackFileCompilation,
            FeatureFlags::TrackFileDefinitions,
            FeatureFlags::TrackSessions,
            FeatureFlags::TrackSerialization,
            FeatureFlags::TrackRegex,
            FeatureFlags::TrackPdo,
            FeatureFlags::TrackMongodb,
            FeatureFlags::TrackElasticsearch,
            FeatureFlags::TrackCaches,
            FeatureFlags::TrackHttp,
            FeatureFlags::TrackMail,
            FeatureFlags::TrackFileOperations,
            FeatureFlags::TrackProc,
            FeatureFlags::TrackProcessList,
            FeatureFlags::TrackErrors,
            FeatureFlags::TrackMagicMethods,
            FeatureFlags::TrackOpcache,
            FeatureFlags::TrackStreams,
        ];
    }

    /**
     * @covers \Perfbase\SDK\FeatureFlags
     */
    public function testIndividualFlagsArePowersOfTwo(): void
    {
        foreach ($this->individualFlags() as $flag) {
            $this->assertSame(0, $flag & ($flag - 1), "Flag $flag is not a power of two");
            $this->assertGreaterThan(0, $flag, "Flag $flag should be greater than zero");
        }
    }

    /**
     * @covers \Perfbase\SDK\FeatureFlags
     */
    public function testAllFlagsUsesTheAllFeaturesSentinel(): void
    {
        $this->assertSame(0, FeatureFlags::AllFlags);
    }

    /**
     * @covers \Perfbase\SDK\FeatureFlags
     */
    public function testRuntimeEventFlagsMatchExtensionBitPositions(): void
    {
        $this->assertSame(1 << 20, FeatureFlags::TrackErrors);
        $this->assertSame(1 << 21, FeatureFlags::TrackMagicMethods);
        $this->assertSame(1 << 22, FeatureFlags::TrackOpcache);
        $this->assertSame(1 << 23, FeatureFlags::TrackStreams);
    }

    /**
     * Bit positions are the extension's PERFBASE_FLAG_* values.
     *
     * @covers \Perfbase\SDK\FeatureFlags
     */
    public function testFlagsMatchExtensionConstants(): void
    {
        $expected = [
            'PERFBASE_FLAG_PRECISE_CLOCK' => FeatureFlags::UsePreciseClock,
            'PERFBASE_FLAG_CPU_TIME' => FeatureFlags::TrackCpuTime,
            'PERFBASE_FLAG_MEMORY' => FeatureFlags::TrackMemoryAllocation,
            'PERFBASE_FLAG_EXCEPTIONS' => FeatureFlags::TrackExceptions,
            'PERFBASE_FLAG_FILE_COMPILATION' => FeatureFlags::TrackFileCompilation,
            'PERFBASE_FLAG_PDO' => FeatureFlags::TrackPdo,
            'PERFBASE_FLAG_MONGODB' => FeatureFlags::TrackMongodb,
            'PERFBASE_FLAG_ELASTICSEARCH' => FeatureFlags::TrackElasticsearch,
            'PERFBASE_FLAG_CACHES' => FeatureFlags::TrackCaches,
            'PERFBASE_FLAG_HTTP' => FeatureFlags::TrackHttp,
            'PERFBASE_FLAG_MAIL' => FeatureFlags::TrackMail,
            'PERFBASE_FLAG_FILE_OPERATIONS' => FeatureFlags::TrackFileOperations,
            'PERFBASE_FLAG_PROC' => FeatureFlags::TrackProc,
            'PERFBASE_FLAG_PROCESS_LIST' => FeatureFlags::TrackProcessList,
            'PERFBASE_FLAG_ERRORS' => FeatureFlags::TrackErrors,
            'PERFBASE_FLAG_MAGIC_METHODS' => FeatureFlags::TrackMagicMethods,
            'PERFBASE_FLAG_OPCACHE' => FeatureFlags::TrackOpcache,
            'PERFBASE_FLAG_STREAMS' => FeatureFlags::TrackStreams,
            'PERFBASE_FLAG_ALL' => FeatureFlags::AllFlags,
        ];

        $values = [
            'PERFBASE_FLAG_PRECISE_CLOCK' => 1 << 0,
            'PERFBASE_FLAG_CPU_TIME' => 1 << 2,
            'PERFBASE_FLAG_MEMORY' => 1 << 3,
            'PERFBASE_FLAG_EXCEPTIONS' => 1 << 5,
            'PERFBASE_FLAG_FILE_COMPILATION' => 1 << 6,
            'PERFBASE_FLAG_PDO' => 1 << 11,
            'PERFBASE_FLAG_MONGODB' => 1 << 12,
            'PERFBASE_FLAG_ELASTICSEARCH' => 1 << 13,
            'PERFBASE_FLAG_CACHES' => 1 << 14,
            'PERFBASE_FLAG_HTTP' => 1 << 15,
            'PERFBASE_FLAG_MAIL' => 1 << 16,
            'PERFBASE_FLAG_FILE_OPERATIONS' => 1 << 17,
            'PERFBASE_FLAG_PROC' => 1 << 18,
            'PERFBASE_FLAG_PROCESS_LIST' => 1 << 19,
            'PERFBASE_FLAG_ERRORS' => 1 << 20,
            'PERFBASE_FLAG_MAGIC_METHODS' => 1 << 21,
            'PERFBASE_FLAG_OPCACHE' => 1 << 22,
            'PERFBASE_FLAG_STREAMS' => 1 << 23,
            'PERFBASE_FLAG_ALL' => 0,
        ];

        foreach ($expected as $constant => $sdkValue) {
            // Compare against the live extension constant when it is loaded.
            $extensionValue = defined($constant) ? constant($constant) : $values[$constant];
            $this->assertSame($extensionValue, $sdkValue, "$constant does not match the SDK flag");
        }
    }

    /**
     * @covers \Perfbase\SDK\FeatureFlags
     */
    public function testDeprecatedFlagsOccupyTheExtensionsReservedBits(): void
    {
        $reserved = (1 << 1) | (1 << 4) | (1 << 7) | (1 << 8) | (1 << 9) | (1 << 10);

        $this->assertSame($reserved, FeatureFlags::DeprecatedFlagsMask);
        $this->assertSame(0, FeatureFlags::DefaultFlags & FeatureFlags::DeprecatedFlagsMask);
        $this->assertSame(
            FeatureFlags::DeprecatedFlagsMask,
            FeatureFlags::ValidFlagsMask & FeatureFlags::DeprecatedFlagsMask,
            'Deprecated flags must keep validating for backwards compatibility'
        );
    }

    /**
     * @covers \Perfbase\SDK\FeatureFlags
     */
    public function testDefaultFlagsExcludeNicheHighOverheadFlags(): void
    {
        $this->assertFalse($this->isFlagEnabled(FeatureFlags::DefaultFlags, FeatureFlags::TrackCpuTime));
        $this->assertFalse($this->isFlagEnabled(FeatureFlags::DefaultFlags, FeatureFlags::TrackMemoryAllocation));
    }

    /**
     * @covers \Perfbase\SDK\FeatureFlags
     */
    public function testDefaultFlagsRetainCoreDefaultCoverage(): void
    {
        $expectedFlags = [
            FeatureFlags::UsePreciseClock,
            FeatureFlags::TrackExceptions,
            FeatureFlags::TrackFileCompilation,
            FeatureFlags::TrackPdo,
            FeatureFlags::TrackMongodb,
            FeatureFlags::TrackElasticsearch,
            FeatureFlags::TrackCaches,
            FeatureFlags::TrackHttp,
            FeatureFlags::TrackMail,
            FeatureFlags::TrackFileOperations,
            FeatureFlags::TrackProc,
            FeatureFlags::TrackProcessList,
            FeatureFlags::TrackErrors,
            FeatureFlags::TrackMagicMethods,
            FeatureFlags::TrackOpcache,
            FeatureFlags::TrackStreams,
        ];

        foreach ($expectedFlags as $flag) {
            $this->assertTrue($this->isFlagEnabled(FeatureFlags::DefaultFlags, $flag));
        }
    }

    /**
     * @covers \Perfbase\SDK\FeatureFlags
     */
    public function testValidFlagsMaskContainsEveryIndividualFlag(): void
    {
        foreach ($this->individualFlags() as $flag) {
            $this->assertSame(
                $flag,
                FeatureFlags::ValidFlagsMask & $flag,
                "ValidFlagsMask should contain flag $flag"
            );
        }
    }

    /**
     * @covers \Perfbase\SDK\FeatureFlags
     */
    public function testFlagCombinationsWorkCorrectly(): void
    {
        $combination = FeatureFlags::TrackCpuTime | FeatureFlags::TrackHttp | FeatureFlags::TrackPdo;

        $this->assertTrue($this->isFlagEnabled($combination, FeatureFlags::TrackCpuTime));
        $this->assertTrue($this->isFlagEnabled($combination, FeatureFlags::TrackHttp));
        $this->assertTrue($this->isFlagEnabled($combination, FeatureFlags::TrackPdo));
        $this->assertFalse($this->isFlagEnabled($combination, FeatureFlags::TrackCaches));
    }

    /**
     * @covers \Perfbase\SDK\FeatureFlags
     */
    public function testValidFlagsMaskIsTheUnionOfAllIndividualFlags(): void
    {
        $expectedMask = 0;

        foreach ($this->individualFlags() as $flag) {
            $expectedMask |= $flag;
        }

        $this->assertSame($expectedMask, FeatureFlags::ValidFlagsMask);
    }

    private function isFlagEnabled(int $flags, int $flag): bool
    {
        return ($flags & $flag) === $flag;
    }
}
