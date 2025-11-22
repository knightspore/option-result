<?php

namespace Ciarancoza\OptionResult;

/**
  * Result<T, E> represents a success (`ok`) or an error (`err`)
  * @template T
  * @template E
*/

class Result { 

    /** 
      * Creates an `ok` result 
      * @param T $value 
      * @return Result<T,never>
    */

    public static function Ok(mixed $value): static {
        return new static($value, true);
    }

    /** 
      * Creates an `err` result 
      * @param E $value 
      * @return Result<never,E>
    */

    public static function Err(mixed $value): static {
        return new static($value, false);
    }

    /** @param T $value */ 

    private function __construct(
        protected mixed $value,
        protected bool $isOk,
    ) {}

    /** Returns `true` if the result is an `ok` result. */

    public function isOk(): bool {
        return $this->isOk;
    }

    /** Returns `true` if the result is an `err` result. */

    public function isErr(): bool {
        return !$this->isOk();
    }

    /**
      * Returns `Some(T)` if `ok`, or `None` if `err`
      * @return Option<T>
    */

    public function getOk(): Option {
        if ($this->isErr()) return Option::None();
        return Option::Some($this->value);
    }

    /**
      * Returns `Some(E)` if `err`, or `None` if `ok`
      * @returns Option<E>
    */

    public function getErr(): Option {
        if ($this->isOk()) return Option::None();
        return Option::Some($this->value);
    }

}
