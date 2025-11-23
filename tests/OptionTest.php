<?php

use PHPUnit\Framework\TestCase;
use Ciarancoza\OptionResult\Option;
use Ciarancoza\OptionResult\Exceptions\UnwrapNoneException;

class OptionTest extends TestCase
{
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
        
        // Test None
        $none = Option::None();
        $this->assertFalse($none->isSome());
        $this->assertTrue($none->isNone());
    }

    public function testValueExtraction(): void
    {
        $this->assertEquals('value', Option::Some('value')->unwrap());
        $this->expectException(UnwrapNoneException::class);
        Option::None()->unwrap();
    }

    public function testUnwrapOrBehavior(): void
    {
        $this->assertEquals('value', Option::Some('value')->unwrapOr('default'));
        $this->assertEquals('default', Option::None()->unwrapOr('default'));
        $this->assertNull(Option::Some(null)->unwrapOr('default'));
        $this->assertEquals(0, Option::None()->unwrapOr(0));
        $defaultObject = (object)['default' => true];
        $this->assertSame($defaultObject, Option::None()->unwrapOr($defaultObject));
    }

    public function testMapTransformation(): void
    {
        // Basic transformation on Some
        $some = Option::Some(5);
        $mapped = $some->map(fn($x) => $x * 2);
        $this->assertTrue($mapped->isSome());
        $this->assertEquals(10, $mapped->unwrap());
        
        // None short-circuits (callback not executed)
        $callbackExecuted = false;
        $noneResult = Option::None()->map(function($x) use (&$callbackExecuted) {
            $callbackExecuted = true;
            return $x * 2;
        });
        $this->assertTrue($noneResult->isNone());
        $this->assertFalse($callbackExecuted);
        
        // Map can return falsy values (they remain Some)
        $this->assertNull($some->map(fn($_) => null)->unwrap());
        
        // Type transformations
        $stringToLength = Option::Some("hello")->map(fn($s) => strlen($s));
        $this->assertEquals(5, $stringToLength->unwrap());
        
        // String transformations
        $upperCase = Option::Some("hello")->map(fn($s) => strtoupper($s));
        $this->assertEquals("HELLO", $upperCase->unwrap());
    }

    public function testMethodChaining(): void
    {
        $this->assertTrue(Option::Some("hello")
            ->map(fn($s) => strtoupper($s))
            ->map(fn($s) => strlen($s))
            ->map(fn($n) => $n > 3)
            ->unwrap());
        
        $this->assertEquals(0, Option::None()
            ->map(fn($s) => strtoupper($s))
            ->map(fn($s) => strlen($s))
            ->unwrapOr(0));
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
