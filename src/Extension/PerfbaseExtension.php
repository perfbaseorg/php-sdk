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
        // It returns NULL when no data is available (or on allocator failure),
        // which must not escape as a TypeError from this string-typed method.
        $data = perfbase_get_data();

        return is_string($data) ? $data : '';
    }

    public function getVersion(): string
    {
        // NULL on allocator failure; normalise so callers can validate it.
        $version = perfbase_version();

        return is_string($version) ? $version : '';
    }

    public function getWireVersion(): int
    {
        return perfbase_wire_version();
    }

    public function getFlags(): int
    {
        return perfbase_get_flags();
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
