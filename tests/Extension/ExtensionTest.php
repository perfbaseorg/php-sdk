<?php

namespace Perfbase\SDK\Tests\Extension;

use Perfbase\SDK\Extension\PerfbaseExtension;
use Perfbase\SDK\Tests\BaseTest;
use Perfbase\SDK\Utils\ExtensionUtils;

/**
 * @coversDefaultClass \Perfbase\SDK\Extension\PerfbaseExtension
 */
class ExtensionTest extends BaseTest
{
    /**
     * @covers ::isAvailable
     */
    public function testIsAvailableReturnsTrueWhenExtensionIsLoaded(): void
    {
        if (!ExtensionUtils::perfbaseExtensionLoaded()) {
            $this->markTestSkipped('Perfbase extension not loaded');
        }

        $extension = new PerfbaseExtension();
        $this->assertTrue($extension->isAvailable());
    }

    /**
     * @covers ::isAvailable
     */
    public function testIsAvailableCachesResult(): void
    {
        $extension = new PerfbaseExtension();
        
        // Call twice to test caching
        $result1 = $extension->isAvailable();
        $result2 = $extension->isAvailable();
        
        $this->assertEquals($result1, $result2);
    }

    /**
     * @covers ::startSpan
     */
    public function testEnable(): void
    {
        if (!ExtensionUtils::perfbaseExtensionLoaded()) {
            $this->markTestSkipped('Perfbase extension not loaded');
        }

        $extension = new PerfbaseExtension();
        
        // This should not throw an exception
        $extension->startSpan('test-span', 0);
        
        // Clean up
        $extension->stopSpan('test-span');
        $extension->reset();
        
        $this->assertTrue(true); // If we get here, no exception was thrown
    }

    /**
     * @covers ::stopSpan
     */
    public function testDisable(): void
    {
        if (!ExtensionUtils::perfbaseExtensionLoaded()) {
            $this->markTestSkipped('Perfbase extension not loaded');
        }

        $extension = new PerfbaseExtension();
        
        // This should not throw an exception
        $extension->stopSpan('test-span');
        
        $this->assertTrue(true); // If we get here, no exception was thrown
    }

    /**
     * @covers ::getSpanData
     */
    public function testGetData(): void
    {
        if (!ExtensionUtils::perfbaseExtensionLoaded()) {
            $this->markTestSkipped('Perfbase extension not loaded');
        }

        $extension = new PerfbaseExtension();
        
        $data = $extension->getSpanData();
        
        $this->assertIsString($data);
    }

    /**
     * @covers ::reset
     */
    public function testReset(): void
    {
        if (!ExtensionUtils::perfbaseExtensionLoaded()) {
            $this->markTestSkipped('Perfbase extension not loaded');
        }

        $extension = new PerfbaseExtension();
        
        // This should not throw an exception
        $extension->reset();
        
        $this->assertTrue(true); // If we get here, no exception was thrown
    }

    /**
     * @covers ::setAttribute
     */
    public function testSetAttribute(): void
    {
        if (!ExtensionUtils::perfbaseExtensionLoaded()) {
            $this->markTestSkipped('Perfbase extension not loaded');
        }

        $extension = new PerfbaseExtension();
        
        // This should not throw an exception
        $extension->setAttribute('test-key', 'test-value');
        
        $this->assertTrue(true); // If we get here, no exception was thrown
    }
}