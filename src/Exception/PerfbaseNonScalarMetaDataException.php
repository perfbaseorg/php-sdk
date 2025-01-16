<?php

namespace Perfbase\SDK\Exception;

class PerfbaseNonScalarMetaDataException extends PerfbaseException
{
    public function __construct()
    {
        parent::__construct('non_scalar_meta');
    }
}