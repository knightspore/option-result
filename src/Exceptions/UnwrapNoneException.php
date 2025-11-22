<?php

namespace Ciarancoza\OptionResult\Exceptions;

class UnwrapNoneException extends \RuntimeException
{
    public function __construct(string $message = 'Attempted to call `unwrap()` on `None` value')
    {
        parent::__construct($message);
    }
}
