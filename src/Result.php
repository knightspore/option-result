<?php

namespace Ciarancoza\OptionResult;

use Ciarancoza\OptionResult\Exceptions\UnwrapErrException;
use Ciarancoza\OptionResult\Exceptions\UnwrapOkException;
use Throwable;

/**
 * Result<T, E> represents a success (`Ok`) or an error (`Err`)
 *
 * @template-covariant T
 * @template-covariant E
 */
final class Result
{
    /**
     * @nodoc
     *
     * @param  T|E  $value
     */
    private function __construct(
        private mixed $value,
        private bool $isOk,
    ) {}

    /**
     * Returns `$res` if the result is `Ok`, otherwise returns the `Err` value of `$this`
     *
     * @template U
     * @template F
     *
     * @param  Result<U,F>  $res
     * @return Result<U,E|F>
     */
    public function and(self $res): Result
    {
        if ($this->isOk()) {
            return $res;
        }

        return self::Err($this->unwrapErr());
    }

    /**
     * Calls `$f` if the result is `Ok`, otherwise returns the `Err` value of `$this`
     *
     * @template U
     * @template F
     *
     * @param  callable(T): Result<U,F>  $f
     * @return Result<U,E|F>
     */
    public function andThen(callable $f): Result
    {
        if ($this->isOk()) {
            return $f($this->unwrap());
        }

        return self::Err($this->unwrapErr());
    }

    /**
     * Creates an `Err` result
     *
     * @template F
     *
     * @param  F  $value
     * @return Result<never,F>
     */
    public static function Err(mixed $value): self
    {
        /** @var Result<never,F> $err */
        $err = new self($value, false);

        return $err;
    }

    /**
     * Returns the contained `Ok` value, or throws UnwrapErrException if the value is an `Err` with a custom panic message provided by `$msg`.
     *
     * @return T
     *
     * @throws UnwrapErrException
     */
    public function expect(string $msg): mixed
    {
        if ($this->isErr()) {
            throw new UnwrapErrException($msg, $this->value);
        }

        return $this->unwrap();
    }

    /**
     * Returns the contained `Err` value, or throws UnwrapOkException if the value is an `Ok` with a custom panic message provided by `$msg`.
     *
     * @return E
     *
     * @throws UnwrapOkException
     */
    public function expectErr(string $msg): mixed
    {
        if ($this->isOk()) {
            throw new UnwrapOkException($msg, $this->value);
        }

        return $this->unwrapErr();
    }

    /**
     * Converts from `Result<T, E>` to `Option<E>`: `Some(E)` if `Err`, or `None` if `Ok`
     *
     * @return Option<E>
     */
    public function getErr(): Option
    {
        if ($this->isErr()) {
            return Option::Some($this->unwrapErr());
        }

        return Option::None();
    }

    /**
     * Converts from `Result<T, E>` to `Option<T>`: `Some(T)` if `Ok`, or `None` if `Err`
     *
     * @return Option<T>
     */
    public function getOk(): Option
    {
        if ($this->isOk()) {
            return Option::Some($this->unwrap());
        }

        return Option::None();
    }

    /**
     * Calls a function with a reference to the contained value if `Ok`
     *
     * @param  callable(T): void  $f
     * @return Result<T,E>
     */
    public function inspect(callable $f): self
    {
        if ($this->isOk()) {
            $f($this->unwrap());
        }

        return $this;
    }

    /**
     * Calls a function with a reference to the contained value if `Err`
     *
     * @param  callable(E): void  $f
     * @return Result<T,E>
     */
    public function inspectErr(callable $f): self
    {
        if ($this->isErr()) {
            $f($this->unwrapErr());
        }

        return $this;
    }

    /**
     * Returns `true` if the result is `Err`
     */
    public function isErr(): bool
    {
        return ! $this->isOk();
    }

    /**
     * Returns `true` if the result is `Ok`
     */
    public function isOk(): bool
    {
        return $this->isOk;
    }

    /**
     * Maps a `Result<T, E>` to `Result<U, E>` by applying a function to a contained `Ok` value, leaving an `Err` value untouched.
     *
     * @template U
     *
     * @param  callable(T): U  $f
     * @return Result<U,E>
     */
    public function map(callable $f): self
    {
        if ($this->isOk()) {
            return self::Ok($f($this->unwrap()));
        }

        return $this;
    }

    /**
     * Maps a `Result<T, E>` to `Result<T, F>` by applying a function to a contained `Err` value, leaving an `Ok` value untouched.
     *
     * @template F
     *
     * @param  callable(E): F  $f
     * @return Result<T,F>
     */
    public function mapErr(callable $f): self
    {
        if ($this->isErr()) {
            return self::Err($f($this->unwrapErr()));
        }

        return $this;
    }

    /**
     * Returns the provided default (if `Err`), or applies a function to the contained value (if `Ok`).
     * If `$default` is a Closure, it will be invoked to get the default value.
     *
     * @template U
     * @template V
     *
     * @param  V|\Closure():V  $default
     * @param  callable(T): U  $f
     * @return U|V
     */
    public function mapOr(mixed $default, callable $f): mixed
    {
        if ($this->isOk()) {
            return $f($this->unwrap());
        }

        return $default instanceof \Closure ? $default() : $default;
    }

    /**
     * Maps a `Result<T, E>` to `U` by applying fallback function `$default` to a contained `Err` value, or function `$f` to a contained `Ok` value.
     *
     * @template U
     * @template V
     *
     * @param  callable(E): V  $default
     * @param  callable(T): U  $f
     * @return U|V
     */
    public function mapOrElse(callable $default, callable $f): mixed
    {
        if ($this->isOk()) {
            return $f($this->unwrap());
        }

        return $default($this->unwrapErr());
    }

    /**
     * Creates an `Ok` result
     *
     * @template U
     *
     * @param  U  $value
     * @return Result<U,never>
     */
    public static function Ok(mixed $value = true): self
    {
        /** @var Result<U,never> $ok */
        $ok = new self($value, true);

        return $ok;
    }

    /**
     * Returns `$res` if the result is `Err`, otherwise returns the `Ok` value of `$this`.
     * If `$res` is a Closure, it will be invoked with the `Err` value to get the alternative result.
     *
     * @template U
     * @template F
     *
     * @param  Result<U,F>|\Closure(E): Result<U,F>  $res
     * @return Result<T|U,F>
     */
    public function or(mixed $res): self
    {
        if ($this->isErr()) {
            return $res instanceof \Closure ? $res($this->unwrapErr()) : $res;
        }

        return self::Ok($this->unwrap());
    }

    /**
     * Calls `$try` and wraps its return value in `Ok`. If `$try` throws, the `Throwable` is passed to `$catch`
     * and the return value is wrapped in `Err`. If no `$catch` is given, the `Throwable` itself becomes the `Err` value.
     *
     * @template U
     * @template F
     *
     * @param  callable(): U  $try
     * @param  null|callable(Throwable): F  $catch
     * @return ($catch is null ? Result<U,Throwable> : Result<U,F>)
     */
    public static function tryCatch(callable $try, ?callable $catch = null): self
    {
        try {
            return self::Ok($try());
        } catch (Throwable $e) {
            if ($catch === null) {
                return self::Err($e);
            }

            return self::Err($catch($e));
        }
    }

    /**
     * Returns the contained `Ok` value or throws UnwrapErrException.
     * The exception carries the `Err` value as `->value`; if that value is a `Throwable` it is also chained as the previous exception.
     *
     * @return T
     *
     * @throws UnwrapErrException
     */
    public function unwrap(): mixed
    {
        if ($this->isErr()) {
            throw new UnwrapErrException(value: $this->value);
        }

        /** @var T $value */
        $value = $this->value;

        return $value;
    }

    /**
     * Returns the contained `Err` value or throws UnwrapOkException. The exception carries the `Ok` value as `->value`.
     *
     * @return E
     *
     * @throws UnwrapOkException
     */
    public function unwrapErr(): mixed
    {
        if ($this->isOk()) {
            throw new UnwrapOkException(value: $this->value);
        }

        /** @var E $value */
        $value = $this->value;

        return $value;
    }

    /**
     * Returns the contained `Ok` value or a provided default.
     * If `$default` is a Closure, it will be invoked to get the default value.
     *
     * @template V
     *
     * @param  V|\Closure():V  $default
     * @return T|V
     */
    public function unwrapOr(mixed $default): mixed
    {
        if ($this->isOk()) {
            return $this->unwrap();
        }

        return $default instanceof \Closure ? $default() : $default;
    }

    /**
     * Returns the contained `Ok` value or computes it from a closure that receives the `Err` value.
     *
     * @template V
     *
     * @param  callable(E): V  $f
     * @return T|V
     */
    public function unwrapOrElse(callable $f): mixed
    {
        if ($this->isOk()) {
            return $this->unwrap();
        }

        return $f($this->unwrapErr());
    }
}
