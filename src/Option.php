<?php

namespace Ciarancoza\OptionResult;

use Ciarancoza\OptionResult\Exceptions\UnwrapNoneException;

/**
 * Option<T> represents an optional value.
 * An option may be `some` or `none`, where `some` contains a value and `none` does not.
 *
 * @template-covariant T
 */
final class Option
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

        return self::None();
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

        return self::None();
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
            return self::Some($this->unwrap());
        }

        return self::None();
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

            return self::Some($this->unwrap());
        }

        return self::None();
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
            return self::Some($f($this->value));
        }

        return self::None();

    }

    /**
     * Returns the provided default (if none), or applies a function to the contained value (if any).
     * If `or` is callable, it will be invoked to get the default value.
     *
     * @template U
     * @template V
     *
     * @param  V|\Closure():V  $or
     * @param  callable(T): U  $f
     * @return U|V
     */
    public function mapOr(mixed $or, callable $f): mixed
    {
        if ($this->isSome()) {
            return $f($this->unwrap());
        }

        return $or instanceof \Closure ? $or() : $or;
    }

    /**
     * Creates a `none` Option
     *
     * @return Option<never>
     */
    public static function None(): self
    {
        /** @var Option<never> $none */
        $none = new self(null, false);

        return $none;
    }

    /**
     * Transforms the `Option<T>` into a `Result<T, E>`, mapping `Some(v)` to `Ok(v)` and `None` to `Err($err)`.
     * If `$err` is a Closure, it will be invoked to get the error value.
     *
     * @template E
     *
     * @param  E|\Closure():E  $err
     * @return Result<T,E>
     */
    public function okOr(mixed $err): Result
    {
        if ($this->isSome()) {
            return Result::Ok($this->unwrap());
        }

        return Result::Err($err instanceof \Closure ? $err() : $err);
    }

    /**
     * Returns the option if it contains a value, otherwise returns `optb`.
     * If `optb` is a Closure, it will be invoked to get the alternative option.
     *
     * @template U
     *
     * @param  Option<U>|\Closure():Option<U>  $optb
     * @return Option<T>|Option<U>
     */
    public function or(mixed $optb): self
    {
        if ($this->isNone()) {
            return $optb instanceof \Closure ? $optb() : $optb;
        }

        return self::Some($this->unwrap());
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
     * @return Option<U>|Option<T>|Option<V>
     */
    public function reduce(self $other, callable $f): self
    {
        return match (true) {
            $this->isSome() && $other->isSome() => self::Some($f($this->unwrap(), $other->unwrap())),
            $this->isSome() => $this,
            $other->isSome() => $other,
            default => self::None(),
        };
    }

    /**
     * Creates a `some` Option
     *
     * @template U
     *
     * @param  U  $value
     * @return Option<U>
     */
    public static function Some(mixed $value = true): self
    {
        if ($value === null) {
            return self::None();
        }

        return new self($value, true);
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
     * @template V
     *
     * @param  V|\Closure():V  $or
     * @return T|V
     */
    public function unwrapOr(mixed $or): mixed
    {
        if ($this->isSome()) {
            return $this->unwrap();
        }

        return $or instanceof \Closure ? $or() : $or;
    }
}
