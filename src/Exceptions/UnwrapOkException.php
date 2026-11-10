<?php

namespace Ciarancoza\OptionResult\Exceptions;

class UnwrapOkException extends \RuntimeException
{
    /**
     * @param  mixed  $value  The contained `Ok` value.
     */
    public function __construct(
        string $message = 'Attempted to call `unwrapErr()` on `Ok` value',
        public readonly mixed $value = null,
    ) {
        parent::__construct($message);
    }
}
