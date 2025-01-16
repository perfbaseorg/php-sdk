<?php

namespace Perfbase\SDK\Exception;

class PerfbaseExtensionMissingException extends PerfbaseException
{
    public function __construct(string $translation = 'extension_missing')
    {
        parent::__construct($translation);
    }
}