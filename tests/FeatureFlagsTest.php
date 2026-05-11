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
            FeatureFlags::TrackWallTime,
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
