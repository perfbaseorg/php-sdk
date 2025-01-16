<?php

namespace Perfbase\SDK\Exception;

class PerfbaseTranslationNotFoundException extends PerfbaseException
{
    public function __construct(string $language, string $key)
    {
        parent::__construct('translation_missing', [$key, $language]);
    }
}