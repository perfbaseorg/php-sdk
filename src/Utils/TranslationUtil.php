<?php

namespace Perfbase\SDK\Utils;

use Perfbase\SDK\Exception\PerfbaseException;
use Perfbase\SDK\Exception\PerfbaseTranslationNotFoundException;

/**
 * Main client class for the Perfbase SDK
 *
 * This class provides the primary interface for interacting with the Perfbase
 * profiling system. It handles starting and stopping profiling sessions and
 * sending the collected data to the Perfbase API.
 *
 * @package Perfbase\SDK
 */
class TranslationUtil
{
    /**
     * The language to use for translations
     * @var string
     */
    private static string $language = 'en';

    /**
     * The available translations
     * @var array<string, array<string, string>>
     */
    private static array $translations = [
        'en' => [
            'api_key_missing' => 'No Perfbase API key provided.',
            'state_exception' => 'Perfbase is in an invalid state to perform this operation.',
            'extension_missing' => 'The required `perfbase` PHP extension is not installed or active',
            'instance_already_active' => 'A profiling instance is already active and cannot be started again.',
            'instance_not_active' => 'No profiling instance is active.',
            'translation_missing' => 'Translation entry "%s" not found in language "%s".',
            'bad_state_transition' => 'Invalid state transition from "%s" to "%s". Required states: %s.',
            'non_scalar_meta' => 'Meta data can only contain scalar values.'
        ]
    ];

    /**
     * Set the language to use for translations
     * @param string $language The language to use, as a 2 character ISO code.
     * @throws PerfbaseException
     */
    public static function setLanguage(string $language): void
    {
        if (!in_array($language, array_keys(self::$translations))) {
            throw new PerfbaseException(sprintf('The requested language "%s" is not available', $language));
        }
        self::$language = $language;
    }

    /**
     * Get a translation for the current language
     * @param string $path The path to the translation, separated by dots.
     * @param array<scalar> $values The values to replace in the translation string.
     * @throws PerfbaseTranslationNotFoundException
     */
    public static function get(string $path, array $values = []): string
    {
        $result = self::$translations[self::$language];
        $keys = explode('.', $path);
        foreach ($keys as $key) {
            if (!isset($result[$key])) {
                throw new PerfbaseTranslationNotFoundException(self::$language, $key);
            }
            $result = $result[$key];
        }
        return sprintf($result, ...$values);
    }

}
