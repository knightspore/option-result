<?php

namespace Ciarancoza\OptionResult;

use Ciarancoza\OptionResult\Exceptions\UnwrapErrException;
use Ciarancoza\OptionResult\Exceptions\UnwrapOkException;

/**
 * Result<T, E> represents a success (`ok`) or an error (`err`)
 *
 * @template T
 * @template E
 */
class Result
{
    /**
     * Creates an `ok` result
     *
     * @param  T  $value
     * @return Result<T,never>
     */
    public static function Ok(mixed $value = true): static
    {
        return new static($value, true);
    }

    /**
     * Creates an `err` result
     *
     * @param  E  $value
     * @return Result<never,E>
     */
    public static function Err(mixed $value): static
    {
        return new static($value, false);
    }

    /** @param T $value */
    private function __construct(
        protected mixed $value,
        protected bool $isOk,
    ) {}

    /** Returns `true` if the result is an `ok` result. */
    public function isOk(): bool
    {
        return $this->isOk;
    }

    /** Returns `true` if the result is an `err` result. */
    public function isErr(): bool
    {
        return ! $this->isOk();
    }

    /**
     * Returns `Some(T)` if `ok`, or `None` if `err`
     *
     * @return Option<T>
     */
    public function getOk(): Option
    {
        if ($this->isErr()) {
            return Option::None();
        }

        return Option::Some($this->value);
    }

    /**
     * Returns `Some(E)` if `err`, or `None` if `ok`
     *
     * @return Option<E>
     */
    public function getErr(): Option
    {
        if ($this->isOk()) {
            return Option::None();
        }

        return Option::Some($this->value);
    }

    /**
     * Returns the contained value if `ok`, otherwise throws UnwrapErrException
     *
     * @return T The contained value
     *
     * @throws UnwrapErrException
     */
    public function unwrap(): mixed
    {
        if ($this->isErr()) {
            throw new UnwrapErrException;
        }

        return $this->value;
    }

    /**
     * Returns the contained value if `err`, otherwise throws UnwrapOkException
     *
     * @return E The contained error value
     *
     * @throws UnwrapOkException
     */
    public function unwrapErr(): mixed
    {
        if ($this->isOk()) {
            throw new UnwrapOkException;
        }

        return $this->value;
    }

    /**
     * Returns the contained `ok` value or a provided default.
     *
     * @param  V  $or
     * @return T|V
     */
    public function unwrapOr(mixed $or): mixed
    {
        if ($this->isOk()) {
            return $this->unwrap();
        }

        return is_callable($or) ? $or() : $or;
    }

    /**
     * Returns the contained `ok` value or computes from closure with error value
     *
     * @param  callable(E): V  $fn
     * @return T|V
     */
    public function unwrapOrElse(callable $fn): mixed
    {
        if ($this->isOk()) {
            return $this->unwrap();
        }

        return $fn($this->value);
    }

    /**
     * If `ok`, transform the value with `$fn`
     *
     * @template U
     *
     * @param  callable(T): U  $fn  Function to transform the value
     * @return Result<U,E>
     */
    public function map(callable $fn): Result
    {
        if ($this->isErr()) {
            return Result::Err($this->value);
        }

        return Result::Ok($fn($this->value));
    }

    /**
     * Calls `fn` on a contained value if `ok`, or returns $or if `err`
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
            $this->isOk() => $fn($this->unwrap()),
            $this->isErr() => is_callable($or) ? $or() : $or,
        };
    }

    /**
     * If `err`, transform the error value with `$fn`
     *
     * @template U
     *
     * @param  callable(E): U  $fn  Function to transform the value
     * @return Result<T,U>
     */
    public function mapErr(callable $fn): Result
    {
        if ($this->isOk()) {
            return Result::Ok($this->value);
        }

        return Result::Err($fn($this->unwrapErr()));
    }
}
