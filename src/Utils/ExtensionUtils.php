<?php

namespace Perfbase\SDK\Utils;

class ExtensionUtils
{
    /**
     * @var array|string[]
     */
    private static array $methods = [
        'perfbase_enable',
        'perfbase_disable',
        'perfbase_reset',
        'perfbase_get_data',
        'perfbase_set_attribute'
    ];

    /**
     * Check if the Perfbase methods are available
     * @return bool
     */
    public static function perfbaseMethodsAvailable(): bool
    {
        foreach (self::$methods as $method) {
            if (!function_exists($method)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Check if the Perfbase extension is loaded
     * @return bool
     */
    public static function perfbaseExtensionLoaded(): bool
    {
        if (!extension_loaded('perfbase')) {
            return false;
        }

        return true;
    }
}
