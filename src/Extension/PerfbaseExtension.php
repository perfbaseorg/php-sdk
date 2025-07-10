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

    /**
     * @param string $spanName
     * @param int $flags
     * @param array<string, string> $attributes
     */
    public function startSpan(string $spanName, int $flags, array $attributes = []): void
    {
        perfbase_enable($spanName, $flags);
        
        // Set initial attributes
        foreach ($attributes as $key => $value) {
            perfbase_set_attribute($key, (string) $value);
        }
    }

    public function stopSpan(string $spanName): void
    {
        perfbase_disable($spanName);
    }

    public function getSpanData(string $spanName = ''): string
    {
        return perfbase_get_data();
    }

    public function setAttribute(string $key, string $value): void
    {
        perfbase_set_attribute($key, $value);
    }

    public function reset(): void
    {
        perfbase_reset();
    }
}