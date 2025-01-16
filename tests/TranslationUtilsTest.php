<?php

namespace Tests;

use Perfbase\SDK\Exception\PerfbaseException;
use Perfbase\SDK\Exception\PerfbaseTranslationNotFoundException;
use Perfbase\SDK\Utils\TranslationUtil;

/**
 * @coversDefaultClass \Perfbase\SDK\Utils\TranslationUtil
 */
class TranslationUtilTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @covers ::setLanguage
     * @return void
     * @throws PerfbaseException
     */
    public function testSetsLanguageSuccessfully(): void
    {
        TranslationUtil::setLanguage('en');

        // No assertions are necessary as the goal is to confirm no exceptions are thrown.
        $this->assertTrue(true);
    }

    /**
     * @covers ::setLanguage
     * @return void
     * @throws PerfbaseException
     */
    public function testThrowsExceptionWhenSettingUnavailableLanguage(): void
    {
        $this->expectException(PerfbaseException::class);
        $this->expectExceptionMessage('The requested language "fr" is not available');

        TranslationUtil::setLanguage('fr');
    }

    /**
     * @covers ::get
     * @return void
     * @throws PerfbaseException
     * @throws PerfbaseTranslationNotFoundException
     */
    public function testRetrievesTranslationSuccessfully(): void
    {
        TranslationUtil::setLanguage('en');

        $translation = TranslationUtil::get('api_key_missing');

        $this->assertSame('No Perfbase API key provided.', $translation);
    }

    /**
     * @covers ::get
     * @return void
     * @throws PerfbaseException
     * @throws PerfbaseTranslationNotFoundException
     */
    public function testRetrievesTranslationWithPlaceholdersSuccessfully(): void
    {
        TranslationUtil::setLanguage('en');

        $translation = TranslationUtil::get('translation_missing', ['key_name', 'en']);

        $this->assertSame('Translation entry "key_name" not found in language "en".', $translation);
    }

    /**
     * @covers ::get
     * @return void
     * @throws PerfbaseException
     * @throws PerfbaseTranslationNotFoundException
     */
    public function testThrowsExceptionWhenTranslationPathDoesNotExist(): void
    {
        TranslationUtil::setLanguage('en');

        $this->expectException(PerfbaseTranslationNotFoundException::class);

        TranslationUtil::get('nonexistent_translation');
    }
}
