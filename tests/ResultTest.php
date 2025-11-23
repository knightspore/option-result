<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use Ciarancoza\OptionResult\Result;
use Ciarancoza\OptionResult\Exceptions\UnwrapErrException;
use Ciarancoza\OptionResult\Exceptions\UnwrapOkException;

class ResultTest extends TestCase
{
    public function testCoreConstructionAndState(): void
    {
        $testValues = ['success', 42, null, false, 0, '', []];
        foreach ($testValues as $value) {
            $ok = Result::Ok($value);
            $this->assertTrue($ok->isOk());
            $this->assertFalse($ok->isErr());
            $this->assertSame($value, $ok->unwrap());
            
            $err = Result::Err($value);
            $this->assertFalse($err->isOk());
            $this->assertTrue($err->isErr());
            $this->assertSame($value, $err->unwrapErr());
        }
        
        $this->assertSame(true, Result::Ok()->unwrap());
    }

    public function testExceptionBehavior(): void
    {
        $this->expectException(UnwrapErrException::class);
        Result::Err('error')->unwrap();
        
        $this->expectException(UnwrapOkException::class);
        Result::Ok('data')->unwrapErr();
        
        foreach ([null, false, 0, '', []] as $value) {
            try {
                Result::Err($value)->unwrap();
                $this->fail("Expected exception for falsy error value");
            } catch (UnwrapErrException $e) {
                $this->assertInstanceOf(UnwrapErrException::class, $e);
            }
        }
    }

    public function testUnwrapOrBehavior(): void
    {
        $this->assertEquals('data', Result::Ok('data')->unwrapOr('default'));
        $this->assertEquals('default', Result::Err('error')->unwrapOr('default'));
        $this->assertNull(Result::Ok(null)->unwrapOr('default'));
        
        $this->assertEquals(0, Result::Err('error')->unwrapOr(0));
        $this->assertFalse(Result::Ok(false)->unwrapOr('default'));
    }

    public function testOptionConversion(): void
    {
        $ok = Result::Ok('data');
        $err = Result::Err('error');
        
        $this->assertTrue($ok->getOk()->isSome());
        $this->assertEquals('data', $ok->getOk()->unwrap());
        $this->assertTrue($err->getOk()->isNone());
        
        $this->assertTrue($err->getErr()->isSome());
        $this->assertEquals('error', $err->getErr()->unwrap());
        $this->assertTrue($ok->getErr()->isNone());
        
        foreach ([null, false, 0, '', []] as $value) {
            $this->assertEquals($value, Result::Ok($value)->getOk()->unwrap());
            $this->assertEquals($value, Result::Err($value)->getErr()->unwrap());
        }
    }

    public function testMapTransformation(): void
    {
        $this->assertEquals(10, Result::Ok(5)->map(fn($x) => $x * 2)->unwrap());
        
        $callbackExecuted = false;
        $errResult = Result::Err('error')->map(function($x) use (&$callbackExecuted) {
            $callbackExecuted = true;
            return $x * 2;
        });
        $this->assertTrue($errResult->isErr());
        $this->assertEquals('error', $errResult->unwrapErr());
        $this->assertFalse($callbackExecuted);
        
        $this->assertNull(Result::Ok(5)->map(fn($_) => null)->unwrap());
        $this->assertEquals(5, Result::Ok("hello")->map(fn($s) => strlen($s))->unwrap());
        
        $this->assertTrue(Result::Ok("hello")
            ->map(fn($s) => strtoupper($s))
            ->map(fn($s) => strlen($s))
            ->map(fn($n) => $n > 3)
            ->unwrap());
        
        $this->assertEquals("connection failed", Result::Err("connection failed")
            ->map(fn($s) => strtoupper($s))
            ->map(fn($s) => strlen($s))
            ->unwrapErr());
    }

    public function testMapErrTransformation(): void
    {
        $this->assertEquals("Error: database error", 
            Result::Err("database error")->mapErr(fn($e) => "Error: " . $e)->unwrapErr());
        
        $callbackExecuted = false;
        $okResult = Result::Ok('data')->mapErr(function($e) use (&$callbackExecuted) {
            $callbackExecuted = true;
            return "Error: " . $e;
        });
        $this->assertEquals('data', $okResult->unwrap());
        $this->assertFalse($callbackExecuted);
        
        $this->assertEquals("ERROR: CONNECTION FAILED", Result::Err("connection")
            ->mapErr(fn($e) => $e . " failed")
            ->mapErr(fn($e) => "Error: " . $e)
            ->mapErr(fn($e) => strtoupper($e))
            ->unwrapErr());
        
        $this->assertEquals("SUCCESS", Result::Ok("success")
            ->map(fn($s) => strtoupper($s))
            ->mapErr(fn($e) => "Error: " . $e)
            ->unwrap());
    }

    public function testMethodChaining(): void
    {
        $this->assertEquals("RESULT: 20", Result::Ok(5)
            ->map(fn($x) => $x * 2)
            ->map(fn($x) => $x + 10)
            ->map(fn($x) => "Result: " . $x)
            ->mapErr(fn($e) => "Enhanced: " . $e)
            ->map(fn($x) => strtoupper($x))
            ->unwrap());
        
        $this->assertEquals("Enhanced: initial error (final)", Result::Err("initial error")
            ->map(fn($x) => $x * 2)
            ->mapErr(fn($e) => "Enhanced: " . $e)
            ->map(fn($x) => strtoupper($x))
            ->mapErr(fn($e) => $e . " (final)")
            ->unwrapErr());
    }

    public function testApiResponseScenario(): void
    {
        $successResponse = ['user' => ['id' => 123, 'name' => 'john doe', 'email' => 'JOHN@EXAMPLE.COM']];
        
        $expected = ['id' => 123, 'name' => 'John doe', 'email' => 'john@example.com'];
        $this->assertEquals($expected, Result::Ok($successResponse)
            ->map(fn($data) => $data['user'])
            ->map(fn($user) => [
                'id' => $user['id'],
                'name' => ucfirst($user['name']),
                'email' => strtolower($user['email'])
            ])->unwrapOr(['error' => 'User not found']));
        
        $this->assertEquals(['error' => 'User not found'], Result::Err("API request failed: 404")
            ->map(fn($data) => $data['user'])
            ->mapErr(fn($error) => "Failed to process user: " . $error)
            ->unwrapOr(['error' => 'User not found']));
    }

    public function testValidationChainScenario(): void
    {
        $processInput = function(string $input) {
            if (empty(trim($input))) return Result::Err("Input cannot be empty");
            $trimmed = trim($input);
            if (strlen($trimmed) > 10) return Result::Err("Input too long");
            return Result::Ok(strtoupper($trimmed));
        };

        $this->assertEquals("HELLO", $processInput("  hello  ")->unwrap());
        $this->assertEquals("Input cannot be empty", $processInput("   ")->unwrapErr());
        $this->assertEquals("Input too long", $processInput("this is way too long")->unwrapErr());
    }

    public function testDataPipelineScenario(): void
    {
        $data = ['users' => [
            ['name' => '  alice  ', 'score' => 85],
            ['name' => 'bob', 'score' => 92],
            ['name' => '', 'score' => 78]
        ]];

        $users = Result::Ok($data)
            ->map(fn($d) => $d['users'])
            ->map(fn($users) => array_filter($users, fn($u) => !empty(trim($u['name']))))
            ->map(fn($users) => array_map(fn($u) => [
                'name' => ucfirst(trim($u['name'])),
                'score' => $u['score'],
                'grade' => $u['score'] >= 90 ? 'A' : ($u['score'] >= 80 ? 'B' : 'C')
            ], $users))->unwrap();

        $this->assertCount(2, $users);
        $this->assertEquals(['name' => 'Alice', 'score' => 85, 'grade' => 'B'], $users[0]);
        $this->assertEquals(['name' => 'Bob', 'score' => 92, 'grade' => 'A'], $users[1]);
    }

    public function testEdgeCases(): void
    {
        $ok = Result::Ok("test");
        $this->assertEquals("test", $ok->unwrap());
        $this->assertEquals("test", $ok->unwrapOr("default"));
        
        $err = Result::Err("error");
        $this->assertEquals("error", $err->unwrapErr());
        $this->assertEquals("default", $err->unwrapOr("default"));
        
        $complexDefault = ['status' => 'error', 'data' => null];
        $this->assertEquals($complexDefault, Result::Err("network timeout")
            ->map(fn($data) => json_decode($data, true))
            ->unwrapOr($complexDefault));
        
        $this->assertEquals("processed", Result::Ok("input")
            ->map(fn($_) => null)
            ->map(fn($_) => "processed")
            ->unwrap());
    }
}