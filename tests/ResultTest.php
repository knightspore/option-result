<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use Ciarancoza\OptionResult\Result;
use Ciarancoza\OptionResult\Option;
use Ciarancoza\OptionResult\Exceptions\UnwrapErrException;
use Ciarancoza\OptionResult\Exceptions\UnwrapOkException;

class ResultTest extends TestCase
{
    // ==============================================
    // Static Constructor Tests
    // ==============================================
    //
    public function testDefaultValue(): void
    {
        $result = Result::Ok();
        $this->assertTrue($result->isOk());
        $this->assertSame(true, $result->unwrap());
    }

    public function testOkConstructor(): void
    {
        $result = Result::Ok("success");
        $this->assertTrue($result->isOk());
        $this->assertFalse($result->isErr());
        $this->assertEquals("success", $result->unwrap());
    }

    public function testOkConstructorWithNumber(): void
    {
        $result = Result::Ok(42);
        $this->assertTrue($result->isOk());
        $this->assertEquals(42, $result->unwrap());
    }

    public function testOkConstructorWithArray(): void
    {
        $data = ['key' => 'value'];
        $result = Result::Ok($data);
        $this->assertTrue($result->isOk());
        $this->assertEquals($data, $result->unwrap());
    }

    public function testOkConstructorWithObject(): void
    {
        $obj = (object) ['prop' => 'value'];
        $result = Result::Ok($obj);
        $this->assertTrue($result->isOk());
        $this->assertEquals($obj, $result->unwrap());
    }

    public function testErrConstructor(): void
    {
        $result = Result::Err("failure");
        $this->assertFalse($result->isOk());
        $this->assertTrue($result->isErr());
        $this->assertEquals("failure", $result->unwrapErr());
    }

    public function testErrConstructorWithNumber(): void
    {
        $result = Result::Err(404);
        $this->assertTrue($result->isErr());
        $this->assertEquals(404, $result->unwrapErr());
    }

    public function testErrConstructorWithArray(): void
    {
        $error = ['code' => 500, 'message' => 'Internal Error'];
        $result = Result::Err($error);
        $this->assertTrue($result->isErr());
        $this->assertEquals($error, $result->unwrapErr());
    }

    // ==============================================
    // Edge Cases with Falsy Values
    // ==============================================

    public function testOkWithNull(): void
    {
        $result = Result::Ok(null);
        $this->assertTrue($result->isOk());
        $this->assertFalse($result->isErr());
        $this->assertNull($result->unwrap());
    }

    public function testOkWithFalse(): void
    {
        $result = Result::Ok(false);
        $this->assertTrue($result->isOk());
        $this->assertFalse($result->unwrap());
    }

    public function testOkWithZero(): void
    {
        $result = Result::Ok(0);
        $this->assertTrue($result->isOk());
        $this->assertEquals(0, $result->unwrap());
    }

    public function testOkWithEmptyString(): void
    {
        $result = Result::Ok("");
        $this->assertTrue($result->isOk());
        $this->assertEquals("", $result->unwrap());
    }

    public function testOkWithEmptyArray(): void
    {
        $result = Result::Ok([]);
        $this->assertTrue($result->isOk());
        $this->assertEquals([], $result->unwrap());
    }

    public function testErrWithNull(): void
    {
        $result = Result::Err(null);
        $this->assertTrue($result->isErr());
        $this->assertNull($result->unwrapErr());
    }

    public function testErrWithFalse(): void
    {
        $result = Result::Err(false);
        $this->assertTrue($result->isErr());
        $this->assertFalse($result->unwrapErr());
    }

    public function testErrWithZero(): void
    {
        $result = Result::Err(0);
        $this->assertTrue($result->isErr());
        $this->assertEquals(0, $result->unwrapErr());
    }

    public function testErrWithEmptyString(): void
    {
        $result = Result::Err("");
        $this->assertTrue($result->isErr());
        $this->assertEquals("", $result->unwrapErr());
    }

    // ==============================================
    // State Checking Tests
    // ==============================================

    public function testIsOkAndIsErr(): void
    {
        $testCases = [
            ['value' => "success", 'isOk' => true, 'expectedIsOk' => true, 'expectedIsErr' => false],
            ['value' => 42, 'isOk' => true, 'expectedIsOk' => true, 'expectedIsErr' => false],
            ['value' => null, 'isOk' => true, 'expectedIsOk' => true, 'expectedIsErr' => false],
            ['value' => false, 'isOk' => true, 'expectedIsOk' => true, 'expectedIsErr' => false],
            ['value' => "", 'isOk' => true, 'expectedIsOk' => true, 'expectedIsErr' => false],
            ['value' => 0, 'isOk' => true, 'expectedIsOk' => true, 'expectedIsErr' => false],
            ['value' => "error", 'isOk' => false, 'expectedIsOk' => false, 'expectedIsErr' => true],
            ['value' => 500, 'isOk' => false, 'expectedIsOk' => false, 'expectedIsErr' => true],
            ['value' => null, 'isOk' => false, 'expectedIsOk' => false, 'expectedIsErr' => true],
            ['value' => false, 'isOk' => false, 'expectedIsOk' => false, 'expectedIsErr' => true],
        ];

        foreach ($testCases as $case) {
            $result = $case['isOk'] ? Result::Ok($case['value']) : Result::Err($case['value']);
            $this->assertEquals($case['expectedIsOk'], $result->isOk());
            $this->assertEquals($case['expectedIsErr'], $result->isErr());
        }
    }

    // ==============================================
    // Value Extraction Tests
    // ==============================================

    public function testUnwrapOnOk(): void
    {
        $result = Result::Ok("data");
        $this->assertEquals("data", $result->unwrap());
    }

    public function testUnwrapOnErr(): void
    {
        $this->expectException(UnwrapErrException::class);
        $result = Result::Err("error");
        $result->unwrap();
    }

    public function testUnwrapErrOnOk(): void
    {
        $this->expectException(UnwrapOkException::class);
        $result = Result::Ok("data");
        $result->unwrapErr();
    }

    public function testUnwrapErrOnErr(): void
    {
        $result = Result::Err("error message");
        $this->assertEquals("error message", $result->unwrapErr());
    }

    public function testUnwrapOr(): void
    {
        $testCases = [
            ['result' => Result::Ok("data"), 'default' => "default", 'expected' => "data"],
            ['result' => Result::Err("error"), 'default' => "default", 'expected' => "default"],
            ['result' => Result::Ok(null), 'default' => "default", 'expected' => null],
            ['result' => Result::Ok(false), 'default' => "default", 'expected' => false],
            ['result' => Result::Ok(0), 'default' => "default", 'expected' => 0],
            ['result' => Result::Ok(""), 'default' => "default", 'expected' => ""],
            ['result' => Result::Err("error"), 'default' => null, 'expected' => null],
            ['result' => Result::Err("error"), 'default' => false, 'expected' => false],
            ['result' => Result::Err("error"), 'default' => 0, 'expected' => 0],
        ];

        foreach ($testCases as $case) {
            $this->assertEquals($case['expected'], $case['result']->unwrapOr($case['default']));
        }
    }

    // ==============================================
    // Option Conversion Tests
    // ==============================================

    public function testGetOkOnOkResult(): void
    {
        $result = Result::Ok("data");
        $option = $result->getOk();
        
        $this->assertInstanceOf(Option::class, $option);
        $this->assertTrue($option->isSome());
        $this->assertEquals("data", $option->unwrap());
    }

    public function testGetOkOnErrResult(): void
    {
        $result = Result::Err("error");
        $option = $result->getOk();
        
        $this->assertInstanceOf(Option::class, $option);
        $this->assertTrue($option->isNone());
    }

    public function testGetErrOnOkResult(): void
    {
        $result = Result::Ok("data");
        $option = $result->getErr();
        
        $this->assertInstanceOf(Option::class, $option);
        $this->assertTrue($option->isNone());
    }

    public function testGetErrOnErrResult(): void
    {
        $result = Result::Err("error");
        $option = $result->getErr();
        
        $this->assertInstanceOf(Option::class, $option);
        $this->assertTrue($option->isSome());
        $this->assertEquals("error", $option->unwrap());
    }

    public function testGetOkWithFalsyValues(): void
    {
        $testCases = [null, false, 0, "", []];
        
        foreach ($testCases as $value) {
            $result = Result::Ok($value);
            $option = $result->getOk();
            
            $this->assertTrue($option->isSome());
            $this->assertEquals($value, $option->unwrap());
        }
    }

    public function testGetErrWithFalsyValues(): void
    {
        $testCases = [null, false, 0, "", []];
        
        foreach ($testCases as $value) {
            $result = Result::Err($value);
            $option = $result->getErr();
            
            $this->assertTrue($option->isSome());
            $this->assertEquals($value, $option->unwrap());
        }
    }

    // ==============================================
    // Map Transformation Tests
    // ==============================================

    public function testMapOnOkResult(): void
    {
        $result = Result::Ok(5);
        $mapped = $result->map(fn($x) => $x * 2);
        
        $this->assertTrue($mapped->isOk());
        $this->assertEquals(10, $mapped->unwrap());
    }

    public function testMapOnErrResult(): void
    {
        $result = Result::Err("database error");
        $mapped = $result->map(fn($x) => $x * 2);
        
        $this->assertTrue($mapped->isErr());
        $this->assertEquals("database error", $mapped->unwrapErr());
    }

    public function testMapChaining(): void
    {
        $result = Result::Ok("hello")
            ->map(fn($s) => strtoupper($s))     // Result::Ok("HELLO")
            ->map(fn($s) => strlen($s))         // Result::Ok(5)
            ->map(fn($n) => $n > 3);            // Result::Ok(true)
        
        $this->assertTrue($result->isOk());
        $this->assertTrue($result->unwrap());
    }

    public function testMapChainingWithError(): void
    {
        $result = Result::Err("connection failed")
            ->map(fn($s) => strtoupper($s))     // Result::Err("connection failed")
            ->map(fn($s) => strlen($s))         // Result::Err("connection failed")
            ->map(fn($n) => $n > 3);            // Result::Err("connection failed")
        
        $this->assertTrue($result->isErr());
        $this->assertEquals("connection failed", $result->unwrapErr());
    }

    public function testMapWithFalsyValues(): void
    {
        // Test that falsy values are properly transformed
        $testCases = [
            [null, fn($x) => "null", "null"],
            [false, fn($x) => "false", "false"],
            [0, fn($x) => $x + 1, 1],
            ["", fn($x) => "empty", "empty"],
            [[], fn($x) => count($x), 0],
        ];

        foreach ($testCases as [$input, $mapper, $expected]) {
            $result = Result::Ok($input)->map($mapper);
            $this->assertTrue($result->isOk());
            $this->assertEquals($expected, $result->unwrap());
        }
    }

    public function testMapWithComplexTransformations(): void
    {
        $data = ['name' => 'john', 'age' => 30];
        $result = Result::Ok($data)
            ->map(fn($user) => $user['name'])
            ->map(fn($name) => ucfirst($name))
            ->map(fn($name) => "Hello, {$name}!");
        
        $this->assertTrue($result->isOk());
        $this->assertEquals("Hello, John!", $result->unwrap());
    }

    // ==============================================
    // MapErr Transformation Tests
    // ==============================================

    public function testMapErrOnErrResult(): void
    {
        $result = Result::Err("database error");
        $mapped = $result->mapErr(fn($e) => "Error: " . $e);
        
        $this->assertTrue($mapped->isErr());
        $this->assertEquals("Error: database error", $mapped->unwrapErr());
    }

    public function testMapErrOnOkResult(): void
    {
        $result = Result::Ok(5);
        $mapped = $result->mapErr(fn($e) => "Error: " . $e);
        
        $this->assertTrue($mapped->isOk());
        $this->assertEquals(5, $mapped->unwrap());
    }

    public function testMapErrChaining(): void
    {
        $result = Result::Err("connection")
            ->mapErr(fn($e) => $e . " failed")          // "connection failed"
            ->mapErr(fn($e) => "Error: " . $e)          // "Error: connection failed"
            ->mapErr(fn($e) => strtoupper($e));         // "ERROR: CONNECTION FAILED"
        
        $this->assertTrue($result->isErr());
        $this->assertEquals("ERROR: CONNECTION FAILED", $result->unwrapErr());
    }

    public function testMapErrWithFalsyValues(): void
    {
        $testCases = [
            [null, fn($x) => "null error", "null error"],
            [false, fn($x) => "false error", "false error"],
            [0, fn($x) => "zero error", "zero error"],
            ["", fn($x) => "empty error", "empty error"],
        ];

        foreach ($testCases as [$input, $mapper, $expected]) {
            $result = Result::Err($input)->mapErr($mapper);
            $this->assertTrue($result->isErr());
            $this->assertEquals($expected, $result->unwrapErr());
        }
    }

    public function testMixedMapAndMapErr(): void
    {
        // Test that map and mapErr can be used together
        $okResult = Result::Ok("success")
            ->map(fn($s) => strtoupper($s))
            ->mapErr(fn($e) => "Error: " . $e);
        
        $this->assertTrue($okResult->isOk());
        $this->assertEquals("SUCCESS", $okResult->unwrap());
        
        $errResult = Result::Err("failure")
            ->map(fn($s) => strtoupper($s))
            ->mapErr(fn($e) => "Error: " . $e);
        
        $this->assertTrue($errResult->isErr());
        $this->assertEquals("Error: failure", $errResult->unwrapErr());
    }

    // ==============================================
    // Exception Testing
    // ==============================================

    public function testUnwrapExceptionOnErr(): void
    {
        $this->expectException(UnwrapErrException::class);
        $result = Result::Err("something failed");
        $result->unwrap();
    }

    public function testUnwrapErrExceptionOnOk(): void
    {
        $this->expectException(UnwrapOkException::class);
        $result = Result::Ok("success");
        $result->unwrapErr();
    }

    public function testExceptionWithFalsyErrorValues(): void
    {
        $falsyValues = [null, false, 0, "", []];
        
        foreach ($falsyValues as $value) {
            try {
                $result = Result::Err($value);
                $result->unwrap();
                $this->fail("Expected UnwrapErrException was not thrown for value: " . var_export($value, true));
            } catch (UnwrapErrException $e) {
                $this->assertInstanceOf(UnwrapErrException::class, $e);
            }
        }
    }

    public function testExceptionWithFalsyOkValues(): void
    {
        $falsyValues = [null, false, 0, "", []];
        
        foreach ($falsyValues as $value) {
            try {
                $result = Result::Ok($value);
                $result->unwrapErr();
                $this->fail("Expected UnwrapOkException was not thrown for value: " . var_export($value, true));
            } catch (UnwrapOkException $e) {
                $this->assertInstanceOf(UnwrapOkException::class, $e);
            }
        }
    }

    // ==============================================
    // Real-World Scenario Tests
    // ==============================================

    public function testApiResponseScenario(): void
    {
        // Simulate successful API response processing
        $successResponse = ['user' => ['id' => 123, 'name' => 'john doe', 'email' => 'JOHN@EXAMPLE.COM']];
        
        $result = Result::Ok($successResponse)
            ->map(fn($data) => $data['user'])
            ->map(fn($user) => [
                'id' => $user['id'],
                'name' => ucfirst($user['name']),
                'email' => strtolower($user['email'])
            ])
            ->unwrapOr(['error' => 'User not found']);
        
        $expected = [
            'id' => 123,
            'name' => 'John doe',
            'email' => 'john@example.com'
        ];
        
        $this->assertEquals($expected, $result);
    }

    public function testApiErrorScenario(): void
    {
        // Simulate failed API response processing
        $result = Result::Err("API request failed: 404")
            ->map(fn($data) => $data['user'])
            ->map(fn($user) => [
                'id' => $user['id'],
                'name' => ucfirst($user['name']),
                'email' => strtolower($user['email'])
            ])
            ->mapErr(fn($error) => "Failed to process user: " . $error)
            ->unwrapOr(['error' => 'User not found']);
        
        $this->assertEquals(['error' => 'User not found'], $result);
    }

    public function testValidationChainScenario(): void
    {
        // Test a validation chain scenario
        $processInput = function(string $input) {
            if (empty(trim($input))) {
                return Result::Err("Input cannot be empty");
            }
            
            $trimmed = trim($input);
            if (strlen($trimmed) > 10) {
                return Result::Err("Input too long");
            }
            
            return Result::Ok(strtoupper($trimmed));
        };

        // Test successful validation
        $result1 = $processInput("  hello  ");
        $this->assertTrue($result1->isOk());
        $this->assertEquals("HELLO", $result1->unwrap());

        // Test empty input
        $result2 = $processInput("   ");
        $this->assertTrue($result2->isErr());
        $this->assertEquals("Input cannot be empty", $result2->unwrapErr());

        // Test too long input
        $result3 = $processInput("this is way too long");
        $this->assertTrue($result3->isErr());
        $this->assertEquals("Input too long", $result3->unwrapErr());
    }

    public function testDataPipelineScenario(): void
    {
        // Test a complex data transformation pipeline
        $data = [
            'users' => [
                ['name' => '  alice  ', 'score' => 85],
                ['name' => 'bob', 'score' => 92],
                ['name' => '', 'score' => 78]
            ]
        ];

        $result = Result::Ok($data)
            ->map(fn($d) => $d['users'])
            ->map(fn($users) => array_filter($users, fn($u) => !empty(trim($u['name']))))
            ->map(fn($users) => array_map(fn($u) => [
                'name' => ucfirst(trim($u['name'])),
                'score' => $u['score'],
                'grade' => $u['score'] >= 90 ? 'A' : ($u['score'] >= 80 ? 'B' : 'C')
            ], $users));

        $this->assertTrue($result->isOk());
        $users = $result->unwrap();
        $this->assertCount(2, $users); // Empty name user filtered out
        $this->assertEquals('Alice', $users[0]['name']);
        $this->assertEquals('B', $users[0]['grade']);
        $this->assertEquals('Bob', $users[1]['name']);
        $this->assertEquals('A', $users[1]['grade']);
    }

    // ==============================================
    // Error Propagation Tests
    // ==============================================

    public function testErrorPropagationThroughLongChain(): void
    {
        $result = Result::Err("initial error")
            ->map(fn($x) => $x * 2)
            ->map(fn($x) => $x + 10)
            ->map(fn($x) => "Result: " . $x)
            ->mapErr(fn($e) => "Enhanced: " . $e)
            ->map(fn($x) => strtoupper($x))
            ->mapErr(fn($e) => $e . " (final)");

        $this->assertTrue($result->isErr());
        $this->assertEquals("Enhanced: initial error (final)", $result->unwrapErr());
    }

    public function testSuccessPropagationThroughLongChain(): void
    {
        $result = Result::Ok(5)
            ->map(fn($x) => $x * 2)         // 10
            ->map(fn($x) => $x + 10)        // 20
            ->map(fn($x) => "Result: " . $x) // "Result: 20"
            ->mapErr(fn($e) => "Enhanced: " . $e) // No effect on Ok
            ->map(fn($x) => strtoupper($x))  // "RESULT: 20"
            ->mapErr(fn($e) => $e . " (final)"); // No effect on Ok

        $this->assertTrue($result->isOk());
        $this->assertEquals("RESULT: 20", $result->unwrap());
    }

    // ==============================================
    // Type Safety and Identity Tests
    // ==============================================

    public function testResultIdentity(): void
    {
        $result1 = Result::Ok("data");
        $result2 = Result::Ok("data");
        $result3 = Result::Err("error");

        // Different instances with same data should not be identical
        $this->assertNotSame($result1, $result2);
        
        // But their values should be equal
        $this->assertEquals($result1->unwrap(), $result2->unwrap());
        
        // Different types should behave differently
        $this->assertNotEquals($result1->isOk(), $result3->isOk());
    }

    public function testResultWithDifferentTypes(): void
    {
        $stringResult = Result::Ok("string");
        $numberResult = Result::Ok(123);
        $arrayResult = Result::Ok(['key' => 'value']);
        $objectResult = Result::Ok((object)['prop' => 'value']);

        $this->assertIsString($stringResult->unwrap());
        $this->assertIsInt($numberResult->unwrap());
        $this->assertIsArray($arrayResult->unwrap());
        $this->assertIsObject($objectResult->unwrap());
    }

    // ==============================================
    // Integration with unwrapOr Tests
    // ==============================================

    public function testComplexChainWithUnwrapOr(): void
    {
        // Test a complete chain ending with unwrapOr for safe extraction
        $processData = function($input) {
            return Result::Ok($input)
                ->map(fn($data) => is_array($data) ? $data : [])
                ->map(fn($arr) => array_filter($arr, fn($x) => $x > 0))
                ->map(fn($arr) => array_sum($arr))
                ->unwrapOr(0);
        };

        $this->assertEquals(15, $processData([1, 2, 3, 4, 5]));
        $this->assertEquals(9, $processData([1, -2, 3, -4, 5]));
        $this->assertEquals(0, $processData([]));
        $this->assertEquals(0, $processData("not an array"));

        // Test with error case
        $errorResult = Result::Err("processing failed")
            ->map(fn($x) => $x * 2)
            ->unwrapOr("default value");
            
        $this->assertEquals("default value", $errorResult);
    }

    public function testUnwrapOrWithComplexDefaults(): void
    {
        $complexDefault = ['status' => 'error', 'data' => null, 'message' => 'Operation failed'];
        
        $result = Result::Err("network timeout")
            ->map(fn($data) => json_decode($data, true))
            ->unwrapOr($complexDefault);
            
        $this->assertEquals($complexDefault, $result);
    }

    // ==============================================
    // Additional Edge Case Tests
    // ==============================================

    public function testResultMethodsPreserveTypeWithCallbacks(): void
    {
        // Test that transformations preserve the Result type through the chain
        $result = Result::Ok(['count' => 5])
            ->map(fn($data) => $data['count'])
            ->map(fn($count) => $count * 2)
            ->map(fn($doubled) => $doubled > 5 ? "high" : "low");

        $this->assertInstanceOf(Result::class, $result);
        $this->assertTrue($result->isOk());
        $this->assertEquals("high", $result->unwrap());
    }

    public function testChainedMethodsWithNullCallbackResults(): void
    {
        // Test behavior when callbacks return null
        $result = Result::Ok("input")
            ->map(fn($s) => null)  // Callback returns null
            ->map(fn($n) => "processed");

        $this->assertTrue($result->isOk());
        $this->assertEquals("processed", $result->unwrap());
    }

    public function testGetMethodsReturnCorrectOptionTypes(): void
    {
        $okResult = Result::Ok("success");
        $errResult = Result::Err("failure");

        // Test getOk returns Some for Ok and None for Err
        $okOption = $okResult->getOk();
        $this->assertTrue($okOption->isSome());
        $this->assertFalse($okOption->isNone());

        $errOkOption = $errResult->getOk();
        $this->assertFalse($errOkOption->isSome());
        $this->assertTrue($errOkOption->isNone());

        // Test getErr returns None for Ok and Some for Err
        $okErrOption = $okResult->getErr();
        $this->assertFalse($okErrOption->isSome());
        $this->assertTrue($okErrOption->isNone());

        $errOption = $errResult->getErr();
        $this->assertTrue($errOption->isSome());
        $this->assertFalse($errOption->isNone());
    }
}
