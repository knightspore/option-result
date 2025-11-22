<?php

namespace Ciarancoza\OptionResult\Exceptions;

class UnwrapOkException extends \RuntimeException
{
    public function __construct(string $message = 'Attempted to call `unwrapErr()` on `Ok` value')
    {
        parent::__construct($message);
    }
}
