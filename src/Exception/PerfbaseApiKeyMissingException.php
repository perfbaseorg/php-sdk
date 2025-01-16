<?php

namespace Perfbase\SDK\Exception;

class PerfbaseApiKeyMissingException extends PerfbaseException
{
    public function __construct()
    {
        parent::__construct('api_key_missing');
    }
}