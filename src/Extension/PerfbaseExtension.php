<?php

namespace Perfbase\SDK\Extension;

use Perfbase\SDK\Utils\ExtensionUtils;

class PerfbaseExtension implements ExtensionInterface
{
    public function isAvailable(): bool
    {
        return ExtensionUtils::perfbaseExtensionLoaded() && ExtensionUtils::perfbaseMethodsAvailable();
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
        // The current extension only supports retrieving the full trace payload.
        return perfbase_get_data();
    }

    public function getVersion(): int
    {
        return perfbase_get_version();
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
