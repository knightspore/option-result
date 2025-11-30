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
     * @nodoc
     *
     * @param  T  $value
     */
    private function __construct(
        private mixed $value,
        private bool $isSome
    ) {}

    /**
     * Returns `None` if the option is `None`, otherwise returns `$optb`
     *
     * @template U
     *
     * @param  Option<U>  $optb
     * @return Option<U>
     */
    public function and(self $optb): Option
    {
        if ($this->isSome()) {
            return $optb;
        }

        return static::None();
    }

    /**
     * Returns `None` if the option is `None`, otherwise calls `$f` with the wrapped value and returns the result.
     *
     * @template U
     *
     * @param  callable(T): Option<U>  $f
     * @return Option<U>
     */
    public function andThen(callable $f): Option
    {
        if ($this->isSome()) {
            return $f($this->unwrap());
        }

        return static::None();
    }

    /**
     * Returns the contained `Some` value, or throws UnwrapNoneException if the value is `None` with a custom panic message provided by `$msg`.
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
     * Returns `None` if the option is `None`, otherwise calls `$predicate` with the wrapped value and returns:
     * - `Some(T)` if `predicate` returns `true` (where `t` is the wrapped value and
     * - `None` if `predicate` returns `false`
     *
     * @param  callable(T): bool  $predicate
     * @return Option<T>
     */
    public function filter(callable $predicate): self
    {
        if ($this->isSome() && $predicate($this->unwrap())) {
            return static::Some($this->unwrap());
        }

        return static::None();
    }

    /**
     * Calls a function with a reference to the contained value if `Some`
     *
     * @param  callable(T): void  $f
     * @return Option<T>
     */
    public function inspect(callable $f): self
    {
        if ($this->isSome()) {
            $f($this->unwrap());

            return static::Some($this->unwrap());
        }

        return static::None();
    }

    /**
     * Returns `true` of the option is a `None` value
     */
    public function isNone(): bool
    {
        return ! $this->isSome();
    }

    /**
     * Returns `true` of the option is a `Some` value
     */
    public function isSome(): bool
    {
        return $this->isSome;
    }

    /**
     * Maps an `Option<T>` to `Option<U>` by applying a function to a contained value (if `Some`) or returns `None` (if `None`)
     *
     * @template U
     *
     * @param  callable(T): U  $f
     * @return Option<U>
     */
    public function map(callable $f): self
    {
        if ($this->isSome()) {
            return static::Some($f($this->value));
        }

        return static::None();

    }

    /**
     * Returns the provided default (if none), or applies a function to the contained value (if any).
     * If `or` is callable, it will be invoked to get the default value.
     *
     * @template U
     * @template V
     *
     * @param  V|callable():V  $or
     * @param  callable(T): U  $f
     * @return U|V
     */
    public function mapOr(mixed $or, callable $f): mixed
    {
        if ($this->isSome()) {
            return $f($this->unwrap());
        }

        return is_callable($or) ? $or() : $or;
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

    /**
     * Returns the option if it contains a value, otherwise returns `optb`.
     * If `optb` is callable, it will be invoked to get the alternative option.
     *
     * @param  Option<T>|callable():Option<T>  $optb
     * @return Option<T>
     */
    public function or(mixed $optb): self
    {
        if ($this->isNone()) {
            return is_callable($optb) ? $optb() : $optb;
        }

        return static::Some($this->unwrap());
    }

    /**
     * Reduces two options into one, using the provided function if both are `Some`.
     *
     * If `$this` is `Some(s)` and `$other` is `Some(o)`, this method returns `Some($f(s,o))`.
     * Otherwise, if only one of `$this` and `$other` is `Some`, that one is returned.
     * If both `$this` and `$other` are `None`, `None` is returned.
     *
     * @template V
     * @template U
     *
     * @param  Option<V>  $other
     * @param  callable(T, V): U  $f
     * @return Option<U|T|V>
     */
    public function reduce(self $other, callable $f): self
    {
        return match (true) {
            $this->isSome() && $other->isSome() => static::Some($f($this->unwrap(), $other->unwrap())),
            $this->isSome() => $this,
            $other->isSome() => $other,
            default => static::None(),
        };
    }

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
            return static::None();
        }

        return new static($value, true);
    }

    /**
     * Returns the contained `Some` value or throws UnwrapNoneException
     *
     * @return T
     *
     * @throws UnwrapNoneException
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
     * If `or` is callable, it will be invoked to get the default value.
     *
     * @param  V|callable():V  $or
     * @return T|V
     */
    public function unwrapOr(mixed $or): mixed
    {
        if ($this->isSome()) {
            return $this->unwrap();
        }

        return is_callable($or) ? $or() : $or;
    }
}
