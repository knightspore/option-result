<?php

/**
 * Static type assertions checked by PHPStan (composer analyse). Not executed by PHPUnit.
 */

namespace Ciarancoza\OptionResult\Tests\Types;

use Ciarancoza\OptionResult\Option;
use Ciarancoza\OptionResult\Result;
use RuntimeException;
use Throwable;

use function PHPStan\Testing\assertType;

final class User
{
    public ?string $theme = null;
}

function constructors(): void
{
    /** @var string|null $nullable */
    $nullable = null;
    assertType('Ciarancoza\OptionResult\Option<string>', Option::Some($nullable));
    assertType('Ciarancoza\OptionResult\Option<Ciarancoza\OptionResult\Tests\Types\User>', Option::Some(new User));
    assertType('Ciarancoza\OptionResult\Option<never>', Option::None());
    assertType('Ciarancoza\OptionResult\Result<int, never>', Result::Ok(1));
    assertType('Ciarancoza\OptionResult\Result<never, string>', Result::Err('e'));
}

/** @param Option<User> $user */
function option_chains(Option $user): void
{
    // Some(null) is None, so the mapped type drops null
    assertType('Ciarancoza\OptionResult\Option<string>', $user->map(fn (User $u) => $u->theme));
    assertType('string', $user->map(fn (User $u) => $u->theme)->unwrapOr('auto'));
    assertType('Ciarancoza\OptionResult\Option<Ciarancoza\OptionResult\Tests\Types\User|int>', $user->or(Option::Some(1)));
    assertType('Ciarancoza\OptionResult\Result<Ciarancoza\OptionResult\Tests\Types\User, string>', $user->okOr('missing'));
}

/** @param Result<User, string> $user */
function result_chains(Result $user): void
{
    assertType('Ciarancoza\OptionResult\Result<int, string>', $user->map(fn (User $u) => 1));
    assertType('Ciarancoza\OptionResult\Result<int, RuntimeException|string>', $user->andThen(fn (User $u) => Result::Ok(1)->mapErr(fn () => new RuntimeException)));
    assertType('int', $user->andThen(fn (User $u) => Result::Ok(1))->unwrap());
    assertType('Ciarancoza\OptionResult\Result<int, string>', $user->and(Result::Ok(1)));
    assertType('Ciarancoza\OptionResult\Result<Ciarancoza\OptionResult\Tests\Types\User|int, never>', $user->or(Result::Ok(1)));
    assertType('Ciarancoza\OptionResult\Result<Ciarancoza\OptionResult\Tests\Types\User, RuntimeException>', $user->mapErr(fn (string $e) => new RuntimeException($e)));
    assertType('Ciarancoza\OptionResult\Option<Ciarancoza\OptionResult\Tests\Types\User>', $user->getOk());
    assertType('Ciarancoza\OptionResult\Option<string>', $user->getErr());
}

function try_catch(): void
{
    assertType('Ciarancoza\OptionResult\Result<Ciarancoza\OptionResult\Tests\Types\User, Throwable>', Result::tryCatch(fn () => new User));
    assertType('Ciarancoza\OptionResult\Result<Ciarancoza\OptionResult\Tests\Types\User, string>', Result::tryCatch(fn () => new User, fn (Throwable $t) => $t->getMessage()));
}
