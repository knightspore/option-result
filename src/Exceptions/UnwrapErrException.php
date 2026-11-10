<?php

namespace Ciarancoza\OptionResult\Exceptions;

use Throwable;

class UnwrapErrException extends \RuntimeException
{
    /**
     * @param  mixed  $value  The contained `Err` value. If it is a `Throwable`, it is also set as the previous exception.
     */
    public function __construct(
        ?string $message = null,
        public readonly mixed $value = null,
    ) {
        $message ??= 'Attempted to call `unwrap()` on `Err` value'.self::describe($value);

        parent::__construct($message, 0, $value instanceof Throwable ? $value : null);
    }

    /**
     * Appends a scalar or Stringable value to the default message so the reason is visible in logs and error trackers.
     */
    private static function describe(mixed $value): string
    {
        if ($value instanceof \Throwable) {
            return ': '.$value->getMessage();
        }

        if (is_scalar($value) || $value instanceof \Stringable) {
            return ': '.$value;
        }

        return '';
    }
}
