<?php

namespace Perfbase\SDK\Extension;

use Perfbase\SDK\Utils\ExtensionUtils;

class PerfbaseExtension implements ExtensionInterface
{
    /**
     * @var bool|null Cached availability status
     */
    private static ?bool $available = null;

    public function isAvailable(): bool
    {
        if (self::$available === null) {
            self::$available = ExtensionUtils::perfbaseExtensionLoaded() && ExtensionUtils::perfbaseMethodsAvailable();
        }
        
        return self::$available;
    }

    public function enable(string $spanName, int $flags): void
    {
        perfbase_enable($spanName, $flags);
    }

    public function disable(string $spanName): void
    {
        perfbase_disable($spanName);
    }

    public function getData(): string
    {
        return perfbase_get_data();
    }

    public function reset(): void
    {
        perfbase_reset();
    }

    public function setAttribute(string $key, string $value): void
    {
        perfbase_set_attribute($key, $value);
    }
}