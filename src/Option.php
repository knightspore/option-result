<?php

namespace Ciarancoza\OptionResult;

use Ciarancoza\OptionResult\Exceptions\UnwrapNoneException;

/**
 * Option<T> represents an optional value.
 * An option may be `some` or `none`, where `some` contains a value and `none` does not.
 * @template T
*/

class Option {

    /**
      * Creates a `some` Option
      * @template T 
      * @param T $value
      * @return Option<T>
    */

    public static function Some(mixed $value): static {
        return new static($value, true);
    }

    /**
      * Creates a `none` Option 
      * @return Option<never> 
    */

    public static function None(): static {
        return new static(null, false);
    }

    /** @param T $value */

    private function __construct(
        private mixed $value, 
        private bool $isSome
    ) {}

    /** Returns `true` if the option is a `some` option. */

    public function isSome(): bool {
        return $this->isSome;
    }

    /** Returns `true` if the option is a `none` option. */

    public function isNone(): bool {
        return !$this->isSome();
    }

    /**
        * Returns the contained value if `some`, otherwise throws UnwrapNoneException.
        * @throws UnwrapNoneException When called on `None`
        * @return T The contained value
    */

    public function unwrap(): mixed {
        if ($this->isNone()) throw new UnwrapNoneException;
        return $this->value;
    }

    /**
        * Returns the contained `some` value or a provided default.
        * @param V $or
        * @return T|V
    */

    public function unwrapOr(mixed $or): mixed {
        if ($this->isSome()) return $this->unwrap();
        return $or;
    }

}
