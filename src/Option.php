<?php

namespace Ciarancoza\OptionResult;

use Ciarancoza\OptionResult\Exceptions\UnwrapNoneException;

/**
 * Option<T> represents an optional value.
 * An option may be `some` or `none`, where `some` contains a value and `none` does not.
 *
 * @template T
 */
class Option
{
    /**
     * Creates a `some` Option
     *
     * @template T
     *
     * @param  T  $value
     * @return Option<T>
     */
    public static function Some(mixed $value = true): static
    {
        if ($value === null) {
            return self::None();
        }

        return new static($value, true);
    }

    /**
     * Creates a `none` Option
     *
     * @return Option<never>
     */
    public static function None(): static
    {
        return new static(null, false);
    }

    /** @param T $value */
    private function __construct(
        private mixed $value,
        private bool $isSome
    ) {}

    /** Returns `true` if the option is a `some` option. */
    public function isSome(): bool
    {
        return $this->isSome;
    }

    /** Returns `true` if the option is a `none` option. */
    public function isNone(): bool
    {
        return ! $this->isSome();
    }

    /**
     * Returns `$and` if `some`, otherwise returns `none`
     *
     * @template V
     *
     * @param  Option<V>  $and
     * @return Option<V>
     */
    public function and(Option $and): mixed
    {
        return match (true) {
            $this->isSome() => $and,
            $this->isNone() => Option::None(),
        };
    }

    /**
     * Returns the contained value if `some`, otherwise throws UnwrapNoneException.
     *
     * @return T The contained value
     *
     * @throws UnwrapNoneException When called on `None`
     */
    public function unwrap(): mixed
    {
        if ($this->isNone()) {
            throw new UnwrapNoneException;
        }

        return $this->value;
    }

    /**
     * Returns the contained `some` value or a provided default.
     *
     * @param  V  $or
     * @return T|V
     */
    public function unwrapOr(mixed $or): mixed
    {
        if ($this->isSome()) {
            return $this->unwrap();
        }

        return is_callable($or) ? $or() : $or;
    }

    /**
     * Calls `fn` on contained value if `some`, returns `none` if `none`
     *
     * @template U
     *
     * @param  callable(T): U  $fn  Function to transform the value
     * @return Option<U>
     */
    public function map(callable $fn): Option
    {
        if ($this->isNone()) {
            return Option::None();
        }

        return Option::Some($fn($this->value));
    }

    /**
     * Calls `fn` on a contained value if `some`, or returns $or if `none`
     *
     * @template V $or
     * @template U
     *
     * @param  callable(T): U  $fn  Function to transform the value
     * @return V|U
     */
    public function mapOr(mixed $or, callable $fn): mixed
    {
        return match (true) {
            $this->isSome() => $fn($this->unwrap()),
            $this->isNone() => is_callable($or) ? $or() : $or,
        };
    }
}
