<?php

declare(strict_types=1);

require_once __DIR__.'/../vendor/autoload.php';

use Ciarancoza\OptionResult\Exceptions\UnwrapErrException;
use Ciarancoza\OptionResult\Exceptions\UnwrapOkException;
use Ciarancoza\OptionResult\Result;
use PHPUnit\Framework\TestCase;

class ResultTest extends TestCase
{
    // Unit tests - Adapted from Rust Docs

    public function test_is_ok(): void
    {
        $x = Result::Ok(-3);
        $this->assertSame(true, $x->isOk());

        $x = Result::Err('Some error message');
        $this->assertSame(false, $x->isOk());
    }

    public function test_is_err(): void
    {
        $x = Result::Ok(-3);
        $this->assertSame(false, $x->isErr());

        $x = Result::Err('Some error message');
        $this->assertSame(true, $x->isErr());
    }

    public function test_unwrap(): void
    {
        $x = Result::Ok(2);
        $this->assertSame(2, $x->unwrap());

        $this->expectException(UnwrapErrException::class);
        Result::Err('emergency failure')->unwrap();
    }

    public function test_unwrap_err(): void
    {
        $x = Result::Err('emergency failure');
        $this->assertSame('emergency failure', $x->unwrapErr());

        $this->expectException(UnwrapOkException::class);
        Result::Ok(2)->unwrapErr();
    }

    public function test_unwrap_or(): void
    {
        $default = 2;
        $this->assertSame(9, Result::Ok(9)->unwrapOr($default));
        $this->assertSame($default, Result::Err('error')->unwrapOr($default));

        $this->assertSame(9, Result::Ok(9)->unwrapOr(fn () => $default));
        $this->assertSame($default, Result::Err('error')->unwrapOr(fn () => $default));
    }

    public function test_unwrap_or_else(): void
    {
        $this->assertSame(2, Result::Ok(2)->unwrapOrElse(fn ($err) => strlen($err)));
        $this->assertSame(3, Result::Err('foo')->unwrapOrElse(fn ($err) => strlen($err)));
    }

    public function test_map(): void
    {
        $result = Result::Ok(5)->map(fn ($i) => $i * 2);
        $this->assertTrue($result->isOk());
        $this->assertSame(10, $result->unwrap());

        $result = Result::Err('parse error')->map(fn ($i) => $i * 2);
        $this->assertTrue($result->isErr());
        $this->assertSame('parse error', $result->unwrapErr());
    }

    public function test_map_err(): void
    {
        $stringify = fn ($x) => "error code: $x";

        $result = Result::Ok(2)->mapErr($stringify);
        $this->assertTrue($result->isOk());
        $this->assertSame(2, $result->unwrap());

        $result = Result::Err(13)->mapErr($stringify);
        $this->assertTrue($result->isErr());
        $this->assertSame('error code: 13', $result->unwrapErr());
    }

    public function test_map_or(): void
    {
        $this->assertSame(3, Result::Ok('foo')->mapOr(42, fn ($v) => strlen($v)));
        $this->assertSame(42, Result::Err('bar')->mapOr(42, fn ($v) => strlen($v)));

        $this->assertSame(3, Result::Ok('foo')->mapOr(fn () => 42, fn ($v) => strlen($v)));
        $this->assertSame(42, Result::Err('bar')->mapOr(fn () => 42, fn ($v) => strlen($v)));
    }

    public function test_map_or_else(): void
    {
        $this->markTestIncomplete('TODO');
        $this->assertSame(3, Result::Ok('foo')->mapOrElse(fn ($err) => strlen($err), fn ($v) => strlen($v)));
        $this->assertSame(3, Result::Err('bar')->mapOrElse(fn ($err) => strlen($err), fn ($v) => strlen($v)));
    }

    public function test_get_ok(): void
    {
        $option = Result::Ok(2)->getOk();
        $this->assertTrue($option->isSome());
        $this->assertSame(2, $option->unwrap());

        $option = Result::Err('Nothing here')->getOk();
        $this->assertTrue($option->isNone());
    }

    public function test_get_err(): void
    {
        $option = Result::Ok(2)->getErr();
        $this->assertTrue($option->isNone());

        $option = Result::Err('Nothing here')->getErr();
        $this->assertTrue($option->isSome());
        $this->assertSame('Nothing here', $option->unwrap());
    }

    public function test_expect(): void
    {
        $this->markTestIncomplete('TODO');
        $this->assertSame('value', Result::Ok('value')->expect('Testing expect'));

        $this->expectException(UnwrapErrException::class);
        $this->expectExceptionMessage('Testing expect');
        Result::Err('error')->expect('Testing expect');
    }

    public function test_expect_err(): void
    {
        $this->markTestIncomplete('TODO');
        $this->assertSame('value', Result::Err('value')->expectErr('Testing expect_err'));

        $this->expectException(UnwrapOkException::class);
        $this->expectExceptionMessage('Testing expect_err');
        Result::Ok('error')->expectErr('Testing expect_err');
    }

    public function test_and_then(): void
    {
        $this->markTestIncomplete('TODO');
        $result = Result::Ok(2)->andThen(fn ($x) => Result::Ok($x * 2));
        $this->assertTrue($result->isOk());
        $this->assertSame(4, $result->unwrap());

        $result = Result::Err('error')->andThen(fn ($x) => Result::Ok($x * 2));
        $this->assertTrue($result->isErr());
        $this->assertSame('error', $result->unwrapErr());

        // Returns Err case
        $result = Result::Ok(2)->andThen(fn ($_) => Result::Err('new error'));
        $this->assertTrue($result->isErr());
        $this->assertSame('new error', $result->unwrapErr());
    }

    public function test_try_catch(): void
    {
        $this->markTestIncomplete('TODO');
        $result = Result::tryCatch(fn () => 'success', fn ($e) => 'Error: '.$e->getMessage());
        $this->assertTrue($result->isOk());
        $this->assertSame('success', $result->unwrap());

        $result = Result::tryCatch(
            fn () => throw new Exception('something failed'),
            fn ($e) => 'Error: '.$e->getMessage()
        );
        $this->assertTrue($result->isErr());
        $this->assertSame('Error: something failed', $result->unwrapErr());
    }

    // Integration tests

    public function test_core_construction_and_state(): void
    {
        $testValues = ['success', 42, false, 0, '', []];
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

        $okNoneType = Result::Ok(null);
        $this->assertTrue($okNoneType->isOk());
        $this->assertFalse($okNoneType->isErr());
        $this->assertNull($okNoneType->unwrap());
        $this->assertEquals('test', $okNoneType->getOk()->unwrapOr('test'));

        $errNoneType = Result::Err(null);
        $this->assertTrue($errNoneType->isErr());
        $this->assertFalse($errNoneType->isOk());
        $this->assertNull($errNoneType->unwrapErr());
        $this->assertEquals('test', $errNoneType->getErr()->unwrapOr('test'));
    }

    public function test_option_conversion(): void
    {
        $ok = Result::Ok('data');
        $err = Result::Err('error');

        $this->assertTrue($ok->getOk()->isSome());
        $this->assertEquals('data', $ok->getOk()->unwrap());
        $this->assertTrue($err->getOk()->isNone());

        $this->assertTrue($err->getErr()->isSome());
        $this->assertEquals('error', $err->getErr()->unwrap());
        $this->assertTrue($ok->getErr()->isNone());

        foreach ([false, 0, '', []] as $value) {
            $this->assertEquals($value, Result::Ok($value)->getOk()->unwrap());
            $this->assertEquals($value, Result::Err($value)->getErr()->unwrap());
        }
    }

    public function test_api_response_scenario(): void
    {
        $successResponse = ['user' => ['id' => 123, 'name' => 'john doe', 'email' => 'JOHN@EXAMPLE.COM']];

        $expected = ['id' => 123, 'name' => 'John doe', 'email' => 'john@example.com'];
        $this->assertEquals($expected, Result::Ok($successResponse)
            ->map(fn ($data) => $data['user'])
            ->map(fn ($user) => [
                'id' => $user['id'],
                'name' => ucfirst($user['name']),
                'email' => strtolower($user['email']),
            ])->unwrapOr(['error' => 'User not found']));

        $this->assertEquals(['error' => 'User not found'], Result::Err('API request failed: 404')
            ->map(fn ($data) => $data['user'])
            ->mapErr(fn ($error) => 'Failed to process user: '.$error)
            ->unwrapOr(['error' => 'User not found']));
    }

    public function test_validation_chain_scenario(): void
    {
        $processInput = function (string $input) {
            if (empty(trim($input))) {
                return Result::Err('Input cannot be empty');
            }
            $trimmed = trim($input);
            if (strlen($trimmed) > 10) {
                return Result::Err('Input too long');
            }

            return Result::Ok(strtoupper($trimmed));
        };

        $this->assertEquals('HELLO', $processInput('  hello  ')->unwrap());
        $this->assertEquals('Input cannot be empty', $processInput('   ')->unwrapErr());
        $this->assertEquals('Input too long', $processInput('this is way too long')->unwrapErr());
    }

    public function test_data_pipeline_scenario(): void
    {
        $data = ['users' => [
            ['name' => '  alice  ', 'score' => 85],
            ['name' => 'bob', 'score' => 92],
            ['name' => '', 'score' => 78],
        ]];

        $users = Result::Ok($data)
            ->map(fn ($d) => $d['users'])
            ->map(fn ($users) => array_filter($users, fn ($u) => ! empty(trim($u['name']))))
            ->map(fn ($users) => array_map(fn ($u) => [
                'name' => ucfirst(trim($u['name'])),
                'score' => $u['score'],
                'grade' => $u['score'] >= 90 ? 'A' : ($u['score'] >= 80 ? 'B' : 'C'),
            ], $users))->unwrap();

        $this->assertCount(2, $users);
        $this->assertEquals(['name' => 'Alice', 'score' => 85, 'grade' => 'B'], $users[0]);
        $this->assertEquals(['name' => 'Bob', 'score' => 92, 'grade' => 'A'], $users[1]);
    }
}
