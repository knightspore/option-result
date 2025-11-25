<?php

use Ciarancoza\OptionResult\Exceptions\UnwrapNoneException;
use Ciarancoza\OptionResult\Option;
use PHPUnit\Framework\TestCase;

class OptionTest extends TestCase
{
    // Unit tests - Adapted from Rust Docs

    public function test_is_none(): void
    {
        $x = Option::Some(2);
        $this->assertSame(false, $x->isNone());

        $x = Option::None();
        $this->assertSame(true, $x->isNone());
    }

    public function test_is_some(): void
    {
        $x = Option::Some(2);
        $this->assertSame(true, $x->isSome());

        $x = Option::None();
        $this->assertSame(false, $x->isSome());
    }

    public function test_unwrap(): void
    {
        $x = Option::Some('air');
        $this->assertSame('air', $x->unwrap());

        $this->expectException(UnwrapNoneException::class);
        Option::None()->unwrap();
    }

    public function test_unwrap_or(): void
    {
        $this->assertSame('car', Option::Some('car')->unwrapOr('bike'));
        $this->assertSame('bike', Option::None()->unwrapOr('bike'));
    }

    public function test_unwrap_or_callable(): void
    {
        $k = 21;
        $this->assertSame(4, Option::Some(4)->unwrapOr(fn () => 2 * $k));
        $this->assertSame(42, Option::None()->unwrapOr(fn () => 2 * $k));
    }

    public function test_map(): void
    {
        $result = Option::Some('Hello World')->map(fn ($s) => strlen($s));
        $this->assertTrue($result->isSome());
        $this->assertSame(11, $result->unwrap());

        $result = Option::None()->map(fn ($s) => strlen($s));
        $this->assertTrue($result->isNone());
    }

    public function test_map_or(): void
    {
        $this->assertSame(3, Option::Some('foo')->mapOr(42, fn ($v) => strlen($v)));
        $this->assertSame(42, Option::None()->mapOr(42, fn ($v) => strlen($v)));

        $this->assertSame(3, Option::Some('foo')->mapOr(fn () => 42, fn ($v) => strlen($v)));
        $this->assertSame(42, Option::None()->mapOr(fn () => 42, fn ($v) => strlen($v)));
    }

    public function test_map_or_else(): void
    {
        $this->markTestIncomplete('TODO');
        $k = 21;
        $this->assertSame(3, Option::Some('foo')->mapOrElse(fn () => 2 * $k, fn ($v) => strlen($v)));
        $this->assertSame(42, Option::None()->mapOrElse(fn () => 2 * $k, fn ($v) => strlen($v)));
    }

    public function test_filter(): void
    {
        $result = Option::Some(4)->filter(fn ($x) => $x > 2);
        $this->assertTrue($result->isSome());
        $this->assertSame(4, $result->unwrap());

        $result = Option::Some(1)->filter(fn ($x) => $x > 2);
        $this->assertTrue($result->isNone());

        $result = Option::None()->filter(fn ($x) => $x > 2);
        $this->assertTrue($result->isNone());
    }

    public function test_expect(): void
    {
        $this->assertSame('value', Option::Some('value')->expect('fruits are healthy'));

        $this->expectException(UnwrapNoneException::class);
        $this->expectExceptionMessage('fruits are healthy');
        Option::None()->expect('fruits are healthy');
    }

    public function test_and(): void
    {
        $result = Option::Some(2)->and(Option::Some(4));
        $this->assertTrue($result->isSome());
        $this->assertSame(4, $result->unwrap());

        $result = Option::None()->and(Option::Some(2));
        $this->assertTrue($result->isNone());

        // Returns None case
        $result = Option::Some(2)->and(Option::None());
        $this->assertTrue($result->isNone());
    }

    public function test_and_then(): void
    {
        $result = Option::Some(2)->andThen(fn ($x) => Option::Some($x * 2));
        $this->assertTrue($result->isSome());
        $this->assertSame(4, $result->unwrap());

        $result = Option::None()->andThen(fn ($x) => Option::Some($x * 2));
        $this->assertTrue($result->isNone());

        // Returns None case
        $result = Option::Some(2)->andThen(fn ($_) => Option::None());
        $this->assertTrue($result->isNone());
    }

    public function test_inspect(): void
    {
        $this->markTestIncomplete('TODO');

        $inspected = null;
        $result = Option::Some(4)->inspect(function ($x) use (&$inspected) {
            $inspected = $x;
        });

        $this->assertTrue($result->isSome());
        $this->assertSame(4, $result->unwrap());
        $this->assertSame(4, $inspected);

        $inspected = null;
        $result = Option::None()->inspect(function ($x) use (&$inspected) {
            $inspected = $x;
        });

        $this->assertTrue($result->isNone());
        $this->assertNull($inspected); // Should not be called
    }

    public function test_reduce(): void
    {
        $this->markTestIncomplete('TODO');

        $s12 = Option::Some(12);
        $s17 = Option::Some(17);
        $n = Option::None();
        $f = fn ($a, $b) => $a + $b;

        $result = $s12->reduce($s17, $f);
        $this->assertTrue($result->isSome());
        $this->assertSame(29, $result->unwrap());

        $result = $s12->reduce($n, $f);
        $this->assertTrue($result->isSome());
        $this->assertSame(12, $result->unwrap());

        $result = $n->reduce($s17, $f);
        $this->assertTrue($result->isSome());
        $this->assertSame(17, $result->unwrap());

        $result = $n->reduce($n, $f);
        $this->assertTrue($result->isNone());
    }

    public function test_replace(): void
    {
        $this->markTestIncomplete('TODO');

        $x = Option::Some(2);
        $old = $x->replace(5);
        $this->assertTrue($x->isSome());
        $this->assertSame(5, $x->unwrap());
        $this->assertTrue($old->isSome());
        $this->assertSame(2, $old->unwrap());

        $x = Option::None();
        $old = $x->replace(3);
        $this->assertTrue($x->isSome());
        $this->assertSame(3, $x->unwrap());
        $this->assertTrue($old->isNone());
    }

    public function test_take(): void
    {
        $this->markTestIncomplete('TODO');

        $x = Option::Some(2);
        $y = $x->take();
        $this->assertTrue($x->isNone());
        $this->assertTrue($y->isSome());
        $this->assertSame(2, $y->unwrap());

        $x = Option::None();
        $y = $x->take();
        $this->assertTrue($x->isNone());
        $this->assertTrue($y->isNone());
    }

    public function test_take_if(): void
    {
        $this->markTestIncomplete('TODO');

        $x = Option::Some(42);
        $prev = $x->takeIf(function (&$v) {
            if ($v === 42) {
                $v += 1;

                return false;
            }

            return false;
        });
        $this->assertTrue($x->isSome());
        $this->assertSame(43, $x->unwrap());
        $this->assertTrue($prev->isNone());

        $x = Option::Some(43);
        $prev = $x->takeIf(fn ($v) => $v === 43);
        $this->assertTrue($x->isNone());
        $this->assertTrue($prev->isSome());
        $this->assertSame(43, $prev->unwrap());

        $x = Option::None();
        $prev = $x->takeIf(fn ($v) => true);
        $this->assertTrue($x->isNone());
        $this->assertTrue($prev->isNone());
    }

    // Integration tests

    public function test_core_construction_and_state(): void
    {
        // Test Some with various values including falsy ones
        $testValues = ['hello', 42, false, 0, '', []];
        foreach ($testValues as $value) {
            $some = Option::Some($value);
            $this->assertFalse($some->isNone());
            $this->assertSame($value, $some->unwrap());
        }

        // Test default value
        $defaultSome = Option::Some();
        $this->assertTrue($defaultSome->isSome());
        $this->assertSame(true, $defaultSome->unwrap());

        // Test null -> None value
        $shouldBeNone = Option::Some(null);
        $this->assertTrue($shouldBeNone->isNone());
        $this->expectException(UnwrapNoneException::class);
        $shouldBeNone->unwrap();
    }

    public function test_email_chain_scenario(): void
    {
        // Simulate the findUserEmail example from USAGE.md
        $users = [
            123 => (object) [
                'id' => 123,
                'profile' => (object) ['email' => 'JOHN@EXAMPLE.COM'],
            ],
            456 => (object) [
                'id' => 456,
                'profile' => null,
            ],
        ];

        $findUser = fn (int $id): Option => isset($users[$id]) ? Option::Some($users[$id]) : Option::None();

        $findUserEmail = function (int $userId) use ($findUser): Option {
            return $findUser($userId)
                ->map(fn ($user) => $user->profile)
                ->map(fn ($profile) => $profile ? $profile->email : null)
                ->map(fn ($email) => $email ? strtolower($email) : null);
        };

        $this->assertEquals('john@example.com', $findUserEmail(123)->unwrapOr('no-email@example.com'));
        $this->assertEquals('no-email@example.com', $findUserEmail(456)->unwrapOr('no-email@example.com')); // Some(null)
        $this->assertEquals('no-email@example.com', $findUserEmail(999)->unwrapOr('no-email@example.com'));
    }

    public function test_edge_cases(): void
    {
        $option = Option::Some('test');
        $this->assertEquals('test', $option->unwrap());
        $this->assertEquals('test', $option->unwrap());
        $this->assertEquals('test', $option->unwrapOr('default'));

        $option = Option::Some(5);
        $this->assertEquals(10, $option->map(fn ($x) => $x * 2)->unwrap());
        $this->assertEquals(15, $option->map(fn ($x) => $x * 3)->unwrap());
        $this->assertEquals(5, $option->unwrap()); // Original unchanged

        $this->expectException(RuntimeException::class);
        Option::Some('test')->map(fn ($_) => throw new RuntimeException('Test exception'));

        // Exception on None does not throw (callback not executed)
        $this->assertTrue(Option::None()->map(fn ($_) => throw new RuntimeException('Should not execute'))->isNone());
    }
}
