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
    public function testPerfbaseExtensionLoadedMatchesPhpExtensionState(): void
    {
        $this->assertSame(extension_loaded('perfbase'), ExtensionUtils::perfbaseExtensionLoaded());
    }

    /**
     * @covers ::perfbaseMethodsAvailable
     */
    public function testPerfbaseMethodsAvailableTracksRequiredFunctions(): void
    {
        $reflection = new \ReflectionClass(ExtensionUtils::class);
        $methodsProperty = $reflection->getProperty('methods');
        $methodsProperty->setAccessible(true);
        $requiredMethods = $methodsProperty->getValue();

        $this->assertSame([
            'perfbase_enable',
            'perfbase_disable',
            'perfbase_reset',
            'perfbase_get_data',
            'perfbase_get_version',
            'perfbase_set_attribute',
        ], $requiredMethods);

        $expectedAvailability = true;
        foreach ($requiredMethods as $method) {
            if (!function_exists($method)) {
                $expectedAvailability = false;
                break;
            }
        }

        $this->assertSame($expectedAvailability, ExtensionUtils::perfbaseMethodsAvailable());
    }
}
