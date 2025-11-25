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
    public function and(self $and): Option
    {
        return match (true) {
            $this->isSome() => $and,
            $this->isNone() => self::None(),
        };
    }

    /**
     * Calls `$then` on contained value and returns if `some`, otherwise returns `none`
     *
     * @template U
     *
     * @param  callable(T): Option<U>  $then  Function to transform the value
     * @return Option<U>
     */
    public function andThen(callable $then): Option
    {
        return match (true) {
            $this->isSome() => $then($this->unwrap()),
            $this->isNone() => self::None(),
        };
    }

    /**
     * Throws UnwrapNoneException with a custom error message if `none`, otherwise returns the inner value
     *
     *
     * @return T
     *
     * @throws UnwrapNoneException
     */
    public function expect(string $msg): mixed
    {
        if ($this->isNone()) {
            throw new UnwrapNoneException($msg);
        }

        return $this->unwrap();
    }

    /**
     * Returns `None` if the option is `None`, otherwise calls `predicate` with the wrapped value and returns:
     * - `Some(T)` if `predicate` returns `true`, and
     * - `None` if `predicate` returns `false`
     *
     * @param  callable(T): bool  $predicate
     */
    public function filter(callable $predicate): self
    {
        if ($this->isSome() && $predicate($this->unwrap())) {
            return $this;
        }

        return self::None();
    }

    /**
     * Calls a function on the contained value if `Some`. Returns the original option in either case.
     *
     * @param  callable(T)  $fn
     */
    public function inspect(callable $fn): self
    {
        if ($this->isSome()) {
            $fn($this->unwrap());
        }

        return $this;
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
    public function map(callable $fn): self
    {
        if ($this->isNone()) {
            return self::None();
        }

        return self::Some($fn($this->value));
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
