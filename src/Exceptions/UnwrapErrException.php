<?php

namespace Ciarancoza\OptionResult\Exceptions;

use Throwable;

class UnwrapErrException extends \RuntimeException
{
    /**
     * @param  mixed  $value  The contained `Err` value. If it is a `Throwable`, it is also set as the previous exception.
     */
    public function __construct(
        string $message = 'Attempted to call `unwrap()` on `Err` value',
        public readonly mixed $value = null,
    ) {
        parent::__construct($message, 0, $value instanceof Throwable ? $value : null);
    }
}
