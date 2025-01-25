<?php

namespace Perfbase\SDK\Exception;

use Exception;
use Throwable;

class PerfbaseException extends Exception
{
    public function __construct(string $message = "")
    {
        parent::__construct($message);
    }
}