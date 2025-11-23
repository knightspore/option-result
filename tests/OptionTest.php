<?php

use PHPUnit\Framework\TestCase;
use Ciarancoza\OptionResult\Option;
use Ciarancoza\OptionResult\Exceptions\UnwrapNoneException;

class OptionTest extends TestCase
{
    // =========================================================================
    // STATIC CONSTRUCTOR TESTS
    // =========================================================================
    
    public function testDefaultValue(): void
    {
        $option = Option::Some();
        $this->assertTrue($option->isSome());
        $this->assertSame(true, $option->unwrap());
    }

    public function testSomeCreatesOptionWithValue(): void
    {
        $option = Option::Some("hello");
        $this->assertTrue($option->isSome());
        $this->assertFalse($option->isNone());
        $this->assertEquals("hello", $option->unwrap());
    }

    public function testSomeCreatesOptionWithNumber(): void
    {
        $option = Option::Some(42);
        $this->assertTrue($option->isSome());
        $this->assertFalse($option->isNone());
        $this->assertEquals(42, $option->unwrap());
    }

    public function testSomeCanExplicitlyContainNull(): void
    {
        $option = Option::Some(null);
        $this->assertTrue($option->isSome());
        $this->assertFalse($option->isNone());
        $this->assertNull($option->unwrap());
    }

    public function testNoneCreatesEmptyOption(): void
    {
        $option = Option::None();
        $this->assertFalse($option->isSome());
        $this->assertTrue($option->isNone());
    }

    public function testSomeWithFalsyValues(): void
    {
        $falsyValues = [
            false,
            0,
            0.0,
            '',
            '0',
            [],
        ];
        
        foreach ($falsyValues as $falsyValue) {
            $option = Option::Some($falsyValue);
            $this->assertTrue($option->isSome());
            $this->assertFalse($option->isNone());
            $this->assertSame($falsyValue, $option->unwrap());
        }
    }

    // =========================================================================
    // STATE CHECKING TESTS
    // =========================================================================

    public function testIsSomeReturnsTrueForSomeValue(): void
    {
        $option = Option::Some("value");
        $this->assertTrue($option->isSome());
    }

    public function testIsSomeReturnsFalseForNone(): void
    {
        $option = Option::None();
        $this->assertFalse($option->isSome());
    }

    public function testIsNoneReturnsFalseForSomeValue(): void
    {
        $option = Option::Some("value");
        $this->assertFalse($option->isNone());
    }

    public function testIsNoneReturnsTrueForNone(): void
    {
        $option = Option::None();
        $this->assertTrue($option->isNone());
    }

    // =========================================================================
    // VALUE EXTRACTION TESTS - unwrap()
    // =========================================================================

    public function testUnwrapReturnsValueFromSome(): void
    {
        $option = Option::Some("hello");
        $this->assertEquals("hello", $option->unwrap());
    }

    public function testUnwrapThrowsExceptionOnNone(): void
    {
        $this->expectException(UnwrapNoneException::class);
        
        $option = Option::None();
        $option->unwrap();
    }

    public function testUnwrapReturnsNullFromSomeNull(): void
    {
        $option = Option::Some(null);
        $this->assertNull($option->unwrap());
    }

    public function testUnwrapReturnsComplexValues(): void
    {
        $complexValues = [
            ['key' => 'value', 'nested' => ['data']],
            (object)['property' => 'value'],
            fn($x) => $x * 2,
        ];
        
        foreach ($complexValues as $complexValue) {
            $option = Option::Some($complexValue);
            $this->assertSame($complexValue, $option->unwrap());
        }
    }

    // =========================================================================
    // VALUE EXTRACTION TESTS - unwrapOr()
    // =========================================================================

    public function testUnwrapOrReturnsValueFromSome(): void
    {
        $option = Option::Some("hello");
        $result = $option->unwrapOr("default");
        $this->assertEquals("hello", $result);
    }

    public function testUnwrapOrReturnsDefaultFromNone(): void
    {
        $option = Option::None();
        $result = $option->unwrapOr("default");
        $this->assertEquals("default", $result);
    }

    public function testUnwrapOrWithNullValue(): void
    {
        // Some(null) should return null, not the default
        $option = Option::Some(null);
        $result = $option->unwrapOr("default");
        $this->assertNull($result);
    }

    public function testUnwrapOrWithFalsyDefaults(): void
    {
        $option = Option::None();
        
        $this->assertFalse($option->unwrapOr(false));
        $this->assertEquals(0, $option->unwrapOr(0));
        $this->assertEquals('', $option->unwrapOr(''));
        $this->assertEquals([], $option->unwrapOr([]));
    }

    public function testUnwrapOrWithComplexDefaults(): void
    {
        $option = Option::None();
        $defaultObject = (object)['default' => true];
        $defaultArray = ['default' => 'value'];
        
        $this->assertSame($defaultObject, $option->unwrapOr($defaultObject));
        $this->assertSame($defaultArray, $option->unwrapOr($defaultArray));
    }

    // =========================================================================
    // TRANSFORMATION TESTS - map()
    // =========================================================================

    public function testMapTransformsValueInSome(): void
    {
        $option = Option::Some(5);
        $doubled = $option->map(fn($x) => $x * 2);
        
        $this->assertTrue($doubled->isSome());
        $this->assertEquals(10, $doubled->unwrap());
    }

    public function testMapOnNoneRetrunsNone(): void
    {
        $option = Option::None();
        $result = $option->map(fn($x) => $x * 2);
        
        $this->assertTrue($result->isNone());
        $this->assertFalse($result->isSome());
    }

    public function testMapDoesNotExecuteCallbackOnNone(): void
    {
        $callbackExecuted = false;
        
        $option = Option::None();
        $option->map(function($x) use (&$callbackExecuted) {
            $callbackExecuted = true;
            return $x * 2;
        });
        
        $this->assertFalse($callbackExecuted, 'Callback should not execute on None');
    }

    public function testMapStringTransformation(): void
    {
        $option = Option::Some("hello");
        $upper = $option->map(fn($s) => strtoupper($s));
        
        $this->assertTrue($upper->isSome());
        $this->assertEquals("HELLO", $upper->unwrap());
    }

    public function testMapWithTypeChanges(): void
    {
        $option = Option::Some("hello");
        $length = $option->map(fn($s) => strlen($s));
        
        $this->assertTrue($length->isSome());
        $this->assertEquals(5, $length->unwrap());
        
        $bool = $length->map(fn($n) => $n > 3);
        $this->assertTrue($bool->isSome());
        $this->assertTrue($bool->unwrap());
    }

    public function testMapCanReturnNull(): void
    {
        $option = Option::Some("test");
        $nullResult = $option->map(fn($s) => null);
        
        $this->assertTrue($nullResult->isSome());
        $this->assertNull($nullResult->unwrap());
    }

    public function testMapCanReturnFalsyValues(): void
    {
        $option = Option::Some("test");
        
        $falseResult = $option->map(fn($s) => false);
        $this->assertTrue($falseResult->isSome());
        $this->assertFalse($falseResult->unwrap());
        
        $zeroResult = $option->map(fn($s) => 0);
        $this->assertTrue($zeroResult->isSome());
        $this->assertEquals(0, $zeroResult->unwrap());
        
        $emptyResult = $option->map(fn($s) => '');
        $this->assertTrue($emptyResult->isSome());
        $this->assertEquals('', $emptyResult->unwrap());
    }

    // =========================================================================
    // METHOD CHAINING TESTS
    // =========================================================================

    public function testBasicChaining(): void
    {
        $result = Option::Some("hello")
            ->map(fn($s) => strtoupper($s))     // "HELLO"
            ->map(fn($s) => strlen($s))         // 5
            ->map(fn($n) => $n > 3);            // true
            
        $this->assertTrue($result->isSome());
        $this->assertTrue($result->unwrap());
    }

    public function testChainingWithNoneSkipsAllTransformations(): void
    {
        $callbackCount = 0;
        
        $result = Option::None()
            ->map(function($s) use (&$callbackCount) {
                $callbackCount++;
                return strtoupper($s);
            })
            ->map(function($s) use (&$callbackCount) {
                $callbackCount++;
                return strlen($s);
            })
            ->unwrapOr(0);
            
        $this->assertEquals(0, $result);
        $this->assertEquals(0, $callbackCount, 'No callbacks should execute on None');
    }

    public function testLongChainWithMixedTransformations(): void
    {
        $result = Option::Some("  hello world  ")
            ->map(fn($s) => trim($s))           // "hello world"
            ->map(fn($s) => explode(' ', $s))   // ["hello", "world"]
            ->map(fn($arr) => count($arr))      // 2
            ->map(fn($n) => $n * 10)            // 20
            ->map(fn($n) => $n > 15);           // true
            
        $this->assertTrue($result->isSome());
        $this->assertTrue($result->unwrap());
    }

    public function testChainingWithUnwrapOr(): void
    {
        $someResult = Option::Some("hello")
            ->map(fn($s) => strtoupper($s))
            ->map(fn($s) => strlen($s))
            ->unwrapOr(0);
            
        $this->assertEquals(5, $someResult);
        
        $noneResult = Option::None()
            ->map(fn($s) => strtoupper($s))
            ->map(fn($s) => strlen($s))
            ->unwrapOr(0);
            
        $this->assertEquals(0, $noneResult);
    }

    // =========================================================================
    // EDGE CASES AND ERROR SCENARIOS
    // =========================================================================

    public function testMapWithExceptionInCallback(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Test exception");
        
        $option = Option::Some("test");
        $option->map(function($s) {
            throw new RuntimeException("Test exception");
        });
    }

    public function testMapWithExceptionOnNoneDoesNotThrow(): void
    {
        // Exception should not be thrown because callback is not executed on None
        $option = Option::None();
        $result = $option->map(function($s) {
            throw new RuntimeException("This should not execute");
        });
        
        $this->assertTrue($result->isNone());
    }

    public function testMultipleUnwrapCallsOnSame(): void
    {
        $option = Option::Some("test");
        
        // Multiple unwrap calls should return the same value
        $this->assertEquals("test", $option->unwrap());
        $this->assertEquals("test", $option->unwrap());
        $this->assertEquals("test", $option->unwrapOr("default"));
    }

    public function testMultipleMapCallsOnSame(): void
    {
        $option = Option::Some(5);
        
        // Multiple map calls should create independent new Options
        $doubled = $option->map(fn($x) => $x * 2);
        $tripled = $option->map(fn($x) => $x * 3);
        
        $this->assertEquals(5, $option->unwrap());
        $this->assertEquals(10, $doubled->unwrap());
        $this->assertEquals(15, $tripled->unwrap());
    }

    // =========================================================================
    // REAL-WORLD SCENARIO TESTS (Based on USAGE.md examples)
    // =========================================================================

    public function testUserFindingScenario(): void
    {
        // Simulate the findUser example from USAGE.md
        $findUser = function(int $id): Option {
            $users = [
                123 => (object)['id' => 123, 'name' => 'john doe'],
                456 => (object)['id' => 456, 'name' => 'jane smith'],
            ];
            
            return isset($users[$id]) ? Option::Some($users[$id]) : Option::None();
        };
        
        $getUserName = function(int $userId) use ($findUser): string {
            return $findUser($userId)
                ->map(fn($user) => $user->name)
                ->map(fn($name) => ucfirst($name))
                ->unwrapOr('Unknown User');
        };
        
        $this->assertEquals('John doe', $getUserName(123));
        $this->assertEquals('Jane smith', $getUserName(456));
        $this->assertEquals('Unknown User', $getUserName(999));
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
        
        // User 123 has email
        $email1 = $findUserEmail(123)->unwrapOr('no-email@example.com');
        $this->assertEquals('john@example.com', $email1);
        
        // User 456 has null profile - the map chain continues but returns null
        $email2 = $findUserEmail(456)->unwrapOr('no-email@example.com');
        $this->assertNull($email2); // Option contains Some(null), so unwrapOr returns null
        
        // Non-existent user - this should return the default
        $email3 = $findUserEmail(999)->unwrapOr('no-email@example.com');
        $this->assertEquals('no-email@example.com', $email3);
    }

    public function testComplexDataProcessingPipeline(): void
    {
        $processUserData = function(array $rawData): Option {
            return Option::Some($rawData)
                ->map(function($data) {
                    // Validate required fields
                    if (empty($data['name']) || empty($data['email'])) {
                        return null;
                    }
                    return $data;
                })
                ->map(function($data) {
                    if ($data === null) return null;
                    // Normalize data
                    return [
                        'name' => trim(ucwords(strtolower($data['name']))),
                        'email' => trim(strtolower($data['email'])),
                        'age' => isset($data['age']) ? (int)$data['age'] : null,
                    ];
                })
                ->map(function($data) {
                    if ($data === null) return null;
                    // Add computed fields
                    $data['initials'] = strtoupper(substr($data['name'], 0, 1));
                    $data['domain'] = explode('@', $data['email'])[1] ?? '';
                    return $data;
                });
        };
        
        // Valid data
        $validResult = $processUserData([
            'name' => '  JOHN DOE  ',
            'email' => '  John.Doe@EXAMPLE.COM  ',
            'age' => '25'
        ])->unwrapOr([]);
        
        $expected = [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'age' => 25,
            'initials' => 'J',
            'domain' => 'example.com'
        ];
        
        $this->assertEquals($expected, $validResult);
        
        // Invalid data (missing email) - Option will contain Some(null)
        $invalidResult = $processUserData([
            'name' => 'John Doe',
            // missing email
        ])->unwrapOr(['error' => 'Invalid data']);
        
        // Since the Option contains Some(null), unwrapOr returns null, not the default
        $this->assertNull($invalidResult);
    }

    // =========================================================================
    // TYPE SAFETY AND CONSISTENCY TESTS
    // =========================================================================

    public function testConsistentBehaviorAcrossDataTypes(): void
    {
        $testValues = [
            'string' => 'hello',
            'integer' => 42,
            'float' => 3.14,
            'boolean_true' => true,
            'boolean_false' => false,
            'null' => null,
            'array' => ['key' => 'value'],
            'object' => (object)['prop' => 'value'],
        ];
        
        foreach ($testValues as $type => $value) {
            $option = Option::Some($value);
            
            $this->assertTrue($option->isSome(), "isSome() should be true for {$type}");
            $this->assertFalse($option->isNone(), "isNone() should be false for {$type}");
            $this->assertSame($value, $option->unwrap(), "unwrap() should return original value for {$type}");
            $this->assertSame($value, $option->unwrapOr('default'), "unwrapOr() should return original value for {$type}");
        }
    }

    public function testMethodReturnTypes(): void
    {
        $some = Option::Some("test");
        $none = Option::None();
        
        // State methods should return boolean
        $this->assertIsBool($some->isSome());
        $this->assertIsBool($some->isNone());
        $this->assertIsBool($none->isSome());
        $this->assertIsBool($none->isNone());
        
        // Map should return Option instance
        $mapped = $some->map(fn($x) => $x);
        $this->assertInstanceOf(Option::class, $mapped);
        
        $mappedNone = $none->map(fn($x) => $x);
        $this->assertInstanceOf(Option::class, $mappedNone);
    }

    // =========================================================================
    // PERFORMANCE AND MEMORY TESTS
    // =========================================================================

    public function testLargeDataHandling(): void
    {
        $largeArray = range(1, 10000);
        $option = Option::Some($largeArray);
        
        $this->assertTrue($option->isSome());
        $this->assertEquals(10000, count($option->unwrap()));
        
        $summed = $option->map(fn($arr) => array_sum($arr));
        $this->assertEquals(50005000, $summed->unwrap()); // Sum of 1 to 10000
    }

    public function testDeepChaining(): void
    {
        $option = Option::Some(1);
        
        // Create a deep chain of 100 transformations
        for ($i = 0; $i < 100; $i++) {
            $option = $option->map(fn($x) => $x + 1);
        }
        
        $this->assertTrue($option->isSome());
        $this->assertEquals(101, $option->unwrap());
    }
}
