
Result<T, E> represents a success (`Ok`) or an error (`Err`)

***

* Full name: `\Ciarancoza\OptionResult\Result`
* This class is marked as **final** and can't be subclassed
* This class is a **Final class**

## Properties

### value

```php
private mixed $value
```

***

### isOk

```php
private bool $isOk
```

***

## Methods

### __construct

```php
private __construct(\Ciarancoza\OptionResult\T|\Ciarancoza\OptionResult\E $value, bool $isOk): mixed
```

**Parameters:**

| Parameter | Type                                                       | Description |
|-----------|------------------------------------------------------------|-------------|
| `$value`  | **\Ciarancoza\OptionResult\T\|\Ciarancoza\OptionResult\E** |             |
| `$isOk`   | **bool**                                                   |             |

***

### and

Returns `$res` if the result is `Ok`, otherwise returns the `Err` value of `$this`

```php
public and(\Ciarancoza\OptionResult\Result<\Ciarancoza\OptionResult\U,\Ciarancoza\OptionResult\F> $res): \Ciarancoza\OptionResult\Result<\Ciarancoza\OptionResult\U,\Ciarancoza\OptionResult\E|\Ciarancoza\OptionResult\F>
```

**Parameters:**

| Parameter | Type                                                                                       | Description |
|-----------|--------------------------------------------------------------------------------------------|-------------|
| `$res`    | **\Ciarancoza\OptionResult\Result<\Ciarancoza\OptionResult\U,\Ciarancoza\OptionResult\F>** |             |

***

### andThen

Calls `$f` if the result is `Ok`, otherwise returns the `Err` value of `$this`

```php
public andThen(callable $f): \Ciarancoza\OptionResult\Result<\Ciarancoza\OptionResult\U,\Ciarancoza\OptionResult\E|\Ciarancoza\OptionResult\F>
```

**Parameters:**

| Parameter | Type         | Description |
|-----------|--------------|-------------|
| `$f`      | **callable** |             |

***

### Err

Creates an `Err` result

```php
public static Err(\Ciarancoza\OptionResult\F $value): \Ciarancoza\OptionResult\Result<never,\Ciarancoza\OptionResult\F>
```

* This method is **static**.
**Parameters:**

| Parameter | Type                           | Description |
|-----------|--------------------------------|-------------|
| `$value`  | **\Ciarancoza\OptionResult\F** |             |

***

### expect

Returns the contained `Ok` value, or throws UnwrapErrException if the value is an `Err` with a custom panic message provided by `$msg`.

```php
public expect(string $msg): \Ciarancoza\OptionResult\T
```

**Parameters:**

| Parameter | Type       | Description |
|-----------|------------|-------------|
| `$msg`    | **string** |             |

**Throws:**

- [`UnwrapErrException`](./Exceptions/UnwrapErrException)

***

### expectErr

Returns the contained `Err` value, or throws UnwrapOkException if the value is an `Ok` with a custom panic message provided by `$msg`.

```php
public expectErr(string $msg): \Ciarancoza\OptionResult\E
```

**Parameters:**

| Parameter | Type       | Description |
|-----------|------------|-------------|
| `$msg`    | **string** |             |

**Throws:**

- [`UnwrapOkException`](./Exceptions/UnwrapOkException)

***

### getErr

Converts from `Result<T, E>` to `Option<E>`: `Some(E)` if `Err`, or `None` if `Ok`

```php
public getErr(): \Ciarancoza\OptionResult\Option<\Ciarancoza\OptionResult\E>
```

***

### getOk

Converts from `Result<T, E>` to `Option<T>`: `Some(T)` if `Ok`, or `None` if `Err`

```php
public getOk(): \Ciarancoza\OptionResult\Option<\Ciarancoza\OptionResult\T>
```

***

### inspect

Calls a function with a reference to the contained value if `Ok`

```php
public inspect(callable $f): \Ciarancoza\OptionResult\Result<\Ciarancoza\OptionResult\T,\Ciarancoza\OptionResult\E>
```

**Parameters:**

| Parameter | Type         | Description |
|-----------|--------------|-------------|
| `$f`      | **callable** |             |

***

### inspectErr

Calls a function with a reference to the contained value if `Err`

```php
public inspectErr(callable $f): \Ciarancoza\OptionResult\Result<\Ciarancoza\OptionResult\T,\Ciarancoza\OptionResult\E>
```

**Parameters:**

| Parameter | Type         | Description |
|-----------|--------------|-------------|
| `$f`      | **callable** |             |

***

### isErr

Returns `true` if the result is `Err`

```php
public isErr(): bool
```

***

### isOk

Returns `true` if the result is `Ok`

```php
public isOk(): bool
```

***

### map

Maps a `Result<T, E>` to `Result<U, E>` by applying a function to a contained `Ok` value, leaving an `Err` value untouched.

```php
public map(callable $f): \Ciarancoza\OptionResult\Result<\Ciarancoza\OptionResult\U,\Ciarancoza\OptionResult\E>
```

**Parameters:**

| Parameter | Type         | Description |
|-----------|--------------|-------------|
| `$f`      | **callable** |             |

***

### mapErr

Maps a `Result<T, E>` to `Result<T, F>` by applying a function to a contained `Err` value, leaving an `Ok` value untouched.

```php
public mapErr(callable $f): \Ciarancoza\OptionResult\Result<\Ciarancoza\OptionResult\T,\Ciarancoza\OptionResult\F>
```

**Parameters:**

| Parameter | Type         | Description |
|-----------|--------------|-------------|
| `$f`      | **callable** |             |

***

### mapOr

Returns the provided default (if `Err`), or applies a function to the contained value (if `Ok`).

```php
public mapOr(\Ciarancoza\OptionResult\V|callable $default, callable $f): \Ciarancoza\OptionResult\U|\Ciarancoza\OptionResult\V
```

If `$default` is a Closure, it will be invoked to get the default value.

**Parameters:**

| Parameter  | Type                                     | Description |
|------------|------------------------------------------|-------------|
| `$default` | **\Ciarancoza\OptionResult\V\|callable** |             |
| `$f`       | **callable**                             |             |

***

### mapOrElse

Maps a `Result<T, E>` to `U` by applying fallback function `$default` to a contained `Err` value, or function `$f` to a contained `Ok` value.

```php
public mapOrElse(callable $default, callable $f): \Ciarancoza\OptionResult\U|\Ciarancoza\OptionResult\V
```

**Parameters:**

| Parameter  | Type         | Description |
|------------|--------------|-------------|
| `$default` | **callable** |             |
| `$f`       | **callable** |             |

***

### Ok

Creates an `Ok` result

```php
public static Ok(\Ciarancoza\OptionResult\U $value = true): \Ciarancoza\OptionResult\Result<\Ciarancoza\OptionResult\U,never>
```

* This method is **static**.
**Parameters:**

| Parameter | Type                           | Description |
|-----------|--------------------------------|-------------|
| `$value`  | **\Ciarancoza\OptionResult\U** |             |

***

### or

Returns `$res` if the result is `Err`, otherwise returns the `Ok` value of `$this`.

```php
public or(\Ciarancoza\OptionResult\Result<\Ciarancoza\OptionResult\U,\Ciarancoza\OptionResult\F>|callable $res): \Ciarancoza\OptionResult\Result<\Ciarancoza\OptionResult\T|\Ciarancoza\OptionResult\U,\Ciarancoza\OptionResult\F>
```

If `$res` is a Closure, it will be invoked with the `Err` value to get the alternative result.

**Parameters:**

| Parameter | Type                                                                                                 | Description |
|-----------|------------------------------------------------------------------------------------------------------|-------------|
| `$res`    | **\Ciarancoza\OptionResult\Result<\Ciarancoza\OptionResult\U,\Ciarancoza\OptionResult\F>\|callable** |             |

***

### tryCatch

Calls `$try` and wraps its return value in `Ok`. If `$try` throws, the `Throwable` is passed to `$catch`
and the return value is wrapped in `Err`. If no `$catch` is given, the `Throwable` itself becomes the `Err` value.

```php
public static tryCatch(callable $try, null|callable $catch = null): ($catch is null ? \Ciarancoza\OptionResult\Result<\Ciarancoza\OptionResult\U,\Throwable> : \Ciarancoza\OptionResult\Result<\Ciarancoza\OptionResult\U,\Ciarancoza\OptionResult\F>)
```

* This method is **static**.
**Parameters:**

| Parameter | Type               | Description |
|-----------|--------------------|-------------|
| `$try`    | **callable**       |             |
| `$catch`  | **null\|callable** |             |

***

### unwrap

Returns the contained `Ok` value or throws UnwrapErrException.

```php
public unwrap(): \Ciarancoza\OptionResult\T
```

The exception carries the `Err` value as `->value`; if that value is a `Throwable` it is also chained as the previous exception.

**Throws:**

- [`UnwrapErrException`](./Exceptions/UnwrapErrException)

***

### unwrapErr

Returns the contained `Err` value or throws UnwrapOkException. The exception carries the `Ok` value as `->value`.

```php
public unwrapErr(): \Ciarancoza\OptionResult\E
```

**Throws:**

- [`UnwrapOkException`](./Exceptions/UnwrapOkException)

***

### unwrapOr

Returns the contained `Ok` value or a provided default.

```php
public unwrapOr(\Ciarancoza\OptionResult\V|callable $default): \Ciarancoza\OptionResult\T|\Ciarancoza\OptionResult\V
```

If `$default` is a Closure, it will be invoked to get the default value.

**Parameters:**

| Parameter  | Type                                     | Description |
|------------|------------------------------------------|-------------|
| `$default` | **\Ciarancoza\OptionResult\V\|callable** |             |

***

### unwrapOrElse

Returns the contained `Ok` value or computes it from a closure that receives the `Err` value.

```php
public unwrapOrElse(callable $f): \Ciarancoza\OptionResult\T|\Ciarancoza\OptionResult\V
```

**Parameters:**

| Parameter | Type         | Description |
|-----------|--------------|-------------|
| `$f`      | **callable** |             |

***
