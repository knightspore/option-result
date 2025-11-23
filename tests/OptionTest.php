<?php

use PHPUnit\Framework\TestCase;
use Ciarancoza\OptionResult\Option;
use Ciarancoza\OptionResult\Exceptions\UnwrapNoneException;

class OptionTest extends TestCase
{

    // Unit tests - Adapted from Rust Docs

    public function testIsNone(): void
    {
        $x = Option::Some(2);
        $this->assertSame(false, $x->isNone());

        $x = Option::None();
        $this->assertSame(true, $x->isNone());
    }

    public function testIsSome(): void
    {
        $x = Option::Some(2);
        $this->assertSame(true, $x->isSome());

        $x = Option::None();
        $this->assertSame(false, $x->isSome());
    }

    public function testUnwrap(): void
    {
        $x = Option::Some("air");
        $this->assertSame("air", $x->unwrap());

        $this->expectException(UnwrapNoneException::class);
        Option::None()->unwrap();
    }

    public function testUnwrapOr(): void
    {
        $this->assertSame("car", Option::Some("car")->unwrapOr("bike"));
        $this->assertSame("bike", Option::None()->unwrapOr("bike"));
    }

    public function testUnwrapOrElse(): void
    {
        $this->markTestIncomplete("TODO");
        $k = 21;
        $this->assertSame(4, Option::Some(4)->unwrapOrElse(fn() => 2 * $k));
        $this->assertSame(42, Option::None()->unwrapOrElse(fn() => 2 * $k));
    }

    public function testMap(): void
    {
        $result = Option::Some("Hello World")->map(fn($s) => strlen($s));
        $this->assertTrue($result->isSome());
        $this->assertSame(11, $result->unwrap());

        $result = Option::None()->map(fn($s) => strlen($s));
        $this->assertTrue($result->isNone());
    }

    public function testMapOr(): void
    {
        $this->markTestIncomplete("TODO");
        $this->assertSame(3, Option::Some("foo")->mapOr(42, fn($v) => strlen($v)));
        $this->assertSame(42, Option::None()->mapOr(42, fn($v) => strlen($v)));
    }

    public function testMapOrElse(): void
    {
        $this->markTestIncomplete("TODO");
        $k = 21;
        $this->assertSame(3, Option::Some("foo")->mapOrElse(fn() => 2 * $k, fn($v) => strlen($v)));
        $this->assertSame(42, Option::None()->mapOrElse(fn() => 2 * $k, fn($v) => strlen($v)));
    }

    public function testFilter(): void
    {
        $this->markTestIncomplete("TODO");
        $result = Option::Some(4)->filter(fn($x) => $x > 2);
        $this->assertTrue($result->isSome());
        $this->assertSame(4, $result->unwrap());

        $result = Option::Some(1)->filter(fn($x) => $x > 2);
        $this->assertTrue($result->isNone());

        $result = Option::None()->filter(fn($x) => $x > 2);
        $this->assertTrue($result->isNone());
    }

    public function testExpect(): void
    {
        $this->markTestIncomplete("TODO");
        $this->assertSame("value", Option::Some("value")->expect("fruits are healthy"));

        $this->expectException(UnwrapNoneException::class);
        $this->expectExceptionMessage("fruits are healthy");
        Option::None()->expect("fruits are healthy");
    }

    public function testAndThen(): void
    {
        $this->markTestIncomplete("TODO");
        $result = Option::Some(2)->andThen(fn($x) => Option::Some($x * 2));
        $this->assertTrue($result->isSome());
        $this->assertSame(4, $result->unwrap());

        $result = Option::None()->andThen(fn($x) => Option::Some($x * 2));
        $this->assertTrue($result->isNone());

        // Returns None case
        $result = Option::Some(2)->andThen(fn($_) => Option::None());
        $this->assertTrue($result->isNone());
    }

    // Integration tests

    public function testCoreConstructionAndState(): void
    {
        // Test Some with various values including falsy ones
        $testValues = ['hello', 42, null, false, 0, '', []];
        foreach ($testValues as $value) {
            $some = Option::Some($value);
            $this->assertTrue($some->isSome());
            $this->assertFalse($some->isNone());
            $this->assertSame($value, $some->unwrap());
        }
        
        // Test default value
        $defaultSome = Option::Some();
        $this->assertTrue($defaultSome->isSome());
        $this->assertSame(true, $defaultSome->unwrap());
    }

    public function testEmailChainScenario(): void
    {
        // Simulate the findUserEmail example from USAGE.md
        $users = [
            123 => (object)[
                'id' => 123,
                'profile' => (object)['email' => 'JOHN@EXAMPLE.COM']
            ],
            456 => (object)[
                'id' => 456,
                'profile' => null
            ],
        ];
        
        $findUser = fn(int $id): Option => 
            isset($users[$id]) ? Option::Some($users[$id]) : Option::None();
            
        $findUserEmail = function(int $userId) use ($findUser): Option {
            return $findUser($userId)
                ->map(fn($user) => $user->profile)
                ->map(fn($profile) => $profile ? $profile->email : null)
                ->map(fn($email) => $email ? strtolower($email) : null);
        };
        
        $this->assertEquals('john@example.com', $findUserEmail(123)->unwrapOr('no-email@example.com'));
        $this->assertNull($findUserEmail(456)->unwrapOr('no-email@example.com')); // Some(null)
        $this->assertEquals('no-email@example.com', $findUserEmail(999)->unwrapOr('no-email@example.com'));
    }

    public function testEdgeCases(): void
    {
        $option = Option::Some("test");
        $this->assertEquals("test", $option->unwrap());
        $this->assertEquals("test", $option->unwrap());
        $this->assertEquals("test", $option->unwrapOr("default"));
        
        $option = Option::Some(5);
        $this->assertEquals(10, $option->map(fn($x) => $x * 2)->unwrap());
        $this->assertEquals(15, $option->map(fn($x) => $x * 3)->unwrap());
        $this->assertEquals(5, $option->unwrap()); // Original unchanged
        
        $this->expectException(RuntimeException::class);
        Option::Some("test")->map(fn($_) => throw new RuntimeException("Test exception"));
        
        // Exception on None does not throw (callback not executed)
        $this->assertTrue(Option::None()->map(fn($_) => throw new RuntimeException("Should not execute"))->isNone());
    }
}
