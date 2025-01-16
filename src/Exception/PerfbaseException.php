<?php

namespace Perfbase\SDK\Exception;

use Exception;
use Perfbase\SDK\Utils\TranslationUtil;
use Throwable;

class PerfbaseException extends Exception
{

    /**
     * @param string $translation
     * @param array<scalar> $values
     * @param int $code
     * @param Throwable|null $previous
     * @throws PerfbaseTranslationNotFoundException
     */
    public function __construct(string $translation, array $values = [], int $code = 0, ?Throwable $previous = null)
    {
        $message = TranslationUtil::get($translation, $values);
        parent::__construct($message, $code, $previous);
    }
}