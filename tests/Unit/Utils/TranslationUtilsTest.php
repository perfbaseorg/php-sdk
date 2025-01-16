<?php

use Perfbase\SDK\Utils\TranslationUtil;
use Perfbase\SDK\Exception\PerfbaseException;
use Perfbase\SDK\Exception\PerfbaseTranslationNotFoundException;

it('sets the language successfully', function () {
    TranslationUtil::setLanguage('en');
    expect(true)->toBeTrue(); // No exceptions should be thrown.
});

it('throws an exception when setting an unavailable language', function () {
    TranslationUtil::setLanguage('fr');
})->throws(PerfbaseException::class, 'The requested language "fr" is not available');

it('retrieves a translation successfully', function () {
    TranslationUtil::setLanguage('en');
    $translation = TranslationUtil::get('api_key_missing');
    expect($translation)->toBe('No Perfbase API key provided.');
});

it('retrieves a translation with placeholders successfully', function () {
    TranslationUtil::setLanguage('en');
    $translation = TranslationUtil::get('translation_missing', ['key_name', 'en']);
    expect($translation)->toBe('Translation entry "key_name" not found in language "en".');
});

it('throws an exception when a translation path does not exist', function () {
    TranslationUtil::setLanguage('en');
    TranslationUtil::get('nonexistent_translation');
})->throws(PerfbaseTranslationNotFoundException::class);