<?php

namespace Ciarancoza\OptionResult\Exceptions;

class UnwrapOkException extends \RuntimeException
{
    /**
     * @param  mixed  $value  The contained `Ok` value.
     */
    public function __construct(
        ?string $message = null,
        public readonly mixed $value = null,
    ) {
        $message ??= 'Attempted to call `unwrapErr()` on `Ok` value'.self::describe($value);

        parent::__construct($message);
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
