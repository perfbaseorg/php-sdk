<?php

namespace Perfbase\SDK\Tests\Utils;

use Perfbase\SDK\Tests\BaseTest;
use Perfbase\SDK\Utils\ExtensionUtils;

/**
 * @coversDefaultClass \Perfbase\SDK\Utils\ExtensionUtils
 */
class ExtensionUtilsTest extends BaseTest
{
    /**
     * @covers ::perfbaseExtensionLoaded
     */
    public function testPerfbaseExtensionLoadedReturnsBooleanValue(): void
    {
        $result = ExtensionUtils::perfbaseExtensionLoaded();
        
        $this->assertIsBool($result);
    }

    /**
     * @covers ::perfbaseMethodsAvailable
     */
    public function testPerfbaseMethodsAvailableReturnsBooleanValue(): void
    {
        $result = ExtensionUtils::perfbaseMethodsAvailable();
        
        $this->assertIsBool($result);
    }

    /**
     * @covers ::perfbaseMethodsAvailable
     */
    public function testPerfbaseMethodsAvailableChecksAllRequiredMethods(): void
    {
        // Get the private methods array using reflection
        $reflection = new \ReflectionClass(ExtensionUtils::class);
        $methodsProperty = $reflection->getProperty('methods');
        $methodsProperty->setAccessible(true);
        $requiredMethods = $methodsProperty->getValue();
        
        $expectedMethods = [
            'perfbase_enable',
            'perfbase_disable',
            'perfbase_reset',
            'perfbase_get_data',
            'perfbase_set_attribute'
        ];
        
        $this->assertEquals($expectedMethods, $requiredMethods);
    }

    /**
     * Test the actual functionality when extension is not loaded
     * @covers ::perfbaseExtensionLoaded
     */
    public function testPerfbaseExtensionLoadedWhenNotLoaded(): void
    {
        // If the extension is not loaded, this should return false
        if (!extension_loaded('perfbase')) {
            $this->assertFalse(ExtensionUtils::perfbaseExtensionLoaded());
        } else {
            $this->assertTrue(ExtensionUtils::perfbaseExtensionLoaded());
        }
    }

    /**
     * Test the actual functionality when methods are not available
     * @covers ::perfbaseMethodsAvailable
     */
    public function testPerfbaseMethodsAvailableWhenMethodsNotDefined(): void
    {
        $result = ExtensionUtils::perfbaseMethodsAvailable();
        
        // This will depend on whether the extension is actually loaded
        // The test verifies the method returns a boolean and doesn't crash
        $this->assertIsBool($result);
        
        // If no extension is loaded, it should return false
        if (!ExtensionUtils::perfbaseExtensionLoaded()) {
            $this->assertFalse($result);
        }
    }

    /**
     * Test that the methods being checked exist in the expected functions
     * @covers ::perfbaseMethodsAvailable
     */
    public function testMethodsArrayContainsExpectedFunctions(): void
    {
        // Use reflection to access the private methods array
        $reflection = new \ReflectionClass(ExtensionUtils::class);
        $methodsProperty = $reflection->getProperty('methods');
        $methodsProperty->setAccessible(true);
        $methods = $methodsProperty->getValue();
        
        // Verify all expected methods are in the array
        $this->assertContains('perfbase_enable', $methods);
        $this->assertContains('perfbase_disable', $methods);
        $this->assertContains('perfbase_reset', $methods);
        $this->assertContains('perfbase_get_data', $methods);
        $this->assertContains('perfbase_set_attribute', $methods);
        
        // Verify the array has exactly 5 methods
        $this->assertCount(5, $methods);
    }

    /**
     * Test integration between the two methods
     * @covers ::perfbaseExtensionLoaded
     * @covers ::perfbaseMethodsAvailable
     */
    public function testIntegrationBetweenExtensionAndMethodChecks(): void
    {
        $extensionLoaded = ExtensionUtils::perfbaseExtensionLoaded();
        $methodsAvailable = ExtensionUtils::perfbaseMethodsAvailable();
        
        // If extension is not loaded, methods should not be available
        if (!$extensionLoaded) {
            $this->assertFalse($methodsAvailable, 
                'Methods should not be available if extension is not loaded');
        }
        
        // Both should return boolean values
        $this->assertIsBool($extensionLoaded);
        $this->assertIsBool($methodsAvailable);
    }

    /**
     * Test that calling methods multiple times gives consistent results
     * @covers ::perfbaseExtensionLoaded
     * @covers ::perfbaseMethodsAvailable
     */
    public function testConsistencyOfResults(): void
    {
        $extensionResult1 = ExtensionUtils::perfbaseExtensionLoaded();
        $extensionResult2 = ExtensionUtils::perfbaseExtensionLoaded();
        
        $methodsResult1 = ExtensionUtils::perfbaseMethodsAvailable();
        $methodsResult2 = ExtensionUtils::perfbaseMethodsAvailable();
        
        $this->assertEquals($extensionResult1, $extensionResult2, 
            'Extension loaded check should be consistent');
        $this->assertEquals($methodsResult1, $methodsResult2, 
            'Methods available check should be consistent');
    }
}