<?php

namespace Perfbase\SDK\Exception;

class PerfbaseStateException extends PerfbaseException
{
    public function __construct(string $translation = 'state_exception', array $values = [])
    {
        parent::__construct($translation, $values);
    }
}