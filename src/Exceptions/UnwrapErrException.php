<?php

namespace Ciarancoza\OptionResult\Exceptions;

class UnwrapErrException extends \RuntimeException
{
    public function __construct(string $message = 'Attempted to call `unwrap()` on `Err` value')
    {
        parent::__construct($message);
    }
}
