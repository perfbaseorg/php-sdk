<?php

namespace Perfbase\SDK\Tests;

use Perfbase\SDK\FeatureFlags;

/**
 * @coversDefaultClass \Perfbase\SDK\FeatureFlags
 */
class FeatureFlagsTest extends BaseTest
{
    /**
     * Test that all individual flags are powers of 2 (bitwise flags)
     * @covers \Perfbase\SDK\FeatureFlags
     */
    public function testIndividualFlagsArePowersOfTwo(): void
    {
        $flags = [
            FeatureFlags::UseCoarseClock,
            FeatureFlags::TrackExceptions,
            FeatureFlags::TrackFileCompilation,
            FeatureFlags::TrackMemoryAllocation,
            FeatureFlags::TrackCpuTime,
            FeatureFlags::TrackFileDefinitions,
            FeatureFlags::TrackPdo,
            FeatureFlags::TrackHttp,
            FeatureFlags::TrackCaches,
            FeatureFlags::TrackMongodb,
            FeatureFlags::TrackElasticsearch,
            FeatureFlags::TrackQueues,
            FeatureFlags::TrackAwsSdk,
            FeatureFlags::TrackFileOperations,
        ];

        foreach ($flags as $flag) {
            // A power of 2 has exactly one bit set, so (n & (n-1)) === 0
            $this->assertEquals(0, $flag & ($flag - 1), "Flag $flag is not a power of 2");
            $this->assertGreaterThan(0, $flag, "Flag $flag should be greater than 0");
        }
    }

    /**
     * Test that DefaultFlags contains expected flags
     * @covers \Perfbase\SDK\FeatureFlags
     */
    public function testDefaultFlagsContainsExpectedFlags(): void
    {
        $expectedFlags = [
            FeatureFlags::UseCoarseClock,
            FeatureFlags::TrackCpuTime,
            FeatureFlags::TrackPdo,
            FeatureFlags::TrackHttp,
            FeatureFlags::TrackCaches,
            FeatureFlags::TrackMongodb,
            FeatureFlags::TrackElasticsearch,
            FeatureFlags::TrackQueues,
            FeatureFlags::TrackAwsSdk,
        ];

        foreach ($expectedFlags as $flag) {
            $this->assertTrue(
                (FeatureFlags::DefaultFlags & $flag) === $flag,
                "DefaultFlags should contain flag $flag"
            );
        }
    }

    /**
     * Test that DefaultFlags does not contain certain flags
     * @covers \Perfbase\SDK\FeatureFlags
     */
    public function testDefaultFlagsDoesNotContainCertainFlags(): void
    {
        $excludedFlags = [
            FeatureFlags::TrackExceptions,
            FeatureFlags::TrackFileCompilation,
            FeatureFlags::TrackMemoryAllocation,
            FeatureFlags::TrackFileDefinitions,
            FeatureFlags::TrackFileOperations,
        ];

        foreach ($excludedFlags as $flag) {
            $this->assertFalse(
                (FeatureFlags::DefaultFlags & $flag) === $flag,
                "DefaultFlags should not contain flag $flag"
            );
        }
    }

    /**
     * Test that AllFlags contains all individual flags
     * @covers \Perfbase\SDK\FeatureFlags
     */
    public function testAllFlagsContainsAllIndividualFlags(): void
    {
        $allFlags = [
            FeatureFlags::UseCoarseClock,
            FeatureFlags::TrackExceptions,
            FeatureFlags::TrackFileCompilation,
            FeatureFlags::TrackMemoryAllocation,
            FeatureFlags::TrackCpuTime,
            FeatureFlags::TrackFileDefinitions,
            FeatureFlags::TrackPdo,
            FeatureFlags::TrackHttp,
            FeatureFlags::TrackCaches,
            FeatureFlags::TrackMongodb,
            FeatureFlags::TrackElasticsearch,
            FeatureFlags::TrackQueues,
            FeatureFlags::TrackAwsSdk,
            FeatureFlags::TrackFileOperations,
        ];

        foreach ($allFlags as $flag) {
            $this->assertTrue(
                (FeatureFlags::AllFlags & $flag) === $flag,
                "AllFlags should contain flag $flag"
            );
        }
    }

    /**
     * Test flag combinations work correctly
     * @covers \Perfbase\SDK\FeatureFlags
     */
    public function testFlagCombinations(): void
    {
        $combination = FeatureFlags::TrackCpuTime | FeatureFlags::TrackHttp;
        
        $this->assertTrue(
            ($combination & FeatureFlags::TrackCpuTime) === FeatureFlags::TrackCpuTime,
            'Combined flags should contain TrackCpuTime'
        );
        
        $this->assertTrue(
            ($combination & FeatureFlags::TrackHttp) === FeatureFlags::TrackHttp,
            'Combined flags should contain TrackHttp'
        );
        
        $this->assertFalse(
            ($combination & FeatureFlags::TrackPdo) === FeatureFlags::TrackPdo,
            'Combined flags should not contain TrackPdo'
        );
    }

    /**
     * Test that we can check if a specific flag is enabled in a combination
     * @covers \Perfbase\SDK\FeatureFlags
     */
    public function testCheckingIndividualFlagsInCombination(): void
    {
        $flags = FeatureFlags::TrackCpuTime | FeatureFlags::TrackHttp | FeatureFlags::TrackPdo;
        
        // Test enabled flags
        $this->assertTrue($this->isFlagEnabled($flags, FeatureFlags::TrackCpuTime));
        $this->assertTrue($this->isFlagEnabled($flags, FeatureFlags::TrackHttp));
        $this->assertTrue($this->isFlagEnabled($flags, FeatureFlags::TrackPdo));
        
        // Test disabled flags
        $this->assertFalse($this->isFlagEnabled($flags, FeatureFlags::TrackCaches));
        $this->assertFalse($this->isFlagEnabled($flags, FeatureFlags::TrackMongodb));
    }

    /**
     * Test flag values are within expected ranges
     * @covers \Perfbase\SDK\FeatureFlags
     */
    public function testFlagValuesAreWithinExpectedRanges(): void
    {
        // All flags should be less than or equal to AllFlags
        $individualFlags = [
            FeatureFlags::UseCoarseClock,
            FeatureFlags::TrackExceptions,
            FeatureFlags::TrackFileCompilation,
            FeatureFlags::TrackMemoryAllocation,
            FeatureFlags::TrackCpuTime,
            FeatureFlags::TrackFileDefinitions,
            FeatureFlags::TrackPdo,
            FeatureFlags::TrackHttp,
            FeatureFlags::TrackCaches,
            FeatureFlags::TrackMongodb,
            FeatureFlags::TrackElasticsearch,
            FeatureFlags::TrackQueues,
            FeatureFlags::TrackAwsSdk,
            FeatureFlags::TrackFileOperations,
        ];

        foreach ($individualFlags as $flag) {
            $this->assertLessThanOrEqual(
                FeatureFlags::AllFlags,
                $flag,
                "Individual flag $flag should be less than or equal to AllFlags"
            );
        }

        // DefaultFlags should be less than AllFlags
        $this->assertLessThan(FeatureFlags::AllFlags, FeatureFlags::DefaultFlags);
    }

    /**
     * Helper method to check if a flag is enabled in a combination
     */
    private function isFlagEnabled(int $flags, int $flag): bool
    {
        return ($flags & $flag) === $flag;
    }
}