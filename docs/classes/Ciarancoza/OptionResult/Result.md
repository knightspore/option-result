
Result<T, E> represents a success (`ok`) or an error (`err`)

***

* Full name: `\Ciarancoza\OptionResult\Result`

## Properties

### value

```php
protected mixed $value
```

***

### isOk

```php
protected bool $isOk
```

***

## Methods

### Ok

Creates an `ok` result

```php
public static Ok(\Ciarancoza\OptionResult\T $value = true): \Ciarancoza\OptionResult\Result<\Ciarancoza\OptionResult\T,never>
```

* This method is **static**.
**Parameters:**

| Parameter | Type                           | Description |
|-----------|--------------------------------|-------------|
| `$value`  | **\Ciarancoza\OptionResult\T** |             |

***

### Err

Creates an `err` result

```php
public static Err(\Ciarancoza\OptionResult\E $value): \Ciarancoza\OptionResult\Result<never,\Ciarancoza\OptionResult\E>
```

* This method is **static**.
**Parameters:**

| Parameter | Type                           | Description |
|-----------|--------------------------------|-------------|
| `$value`  | **\Ciarancoza\OptionResult\E** |             |

***

### __construct

```php
private __construct(\Ciarancoza\OptionResult\T $value, bool $isOk): mixed
```

**Parameters:**

| Parameter | Type                           | Description |
|-----------|--------------------------------|-------------|
| `$value`  | **\Ciarancoza\OptionResult\T** |             |
| `$isOk`   | **bool**                       |             |

***

### isOk

Returns `true` if the result is an `ok` result.

```php
public isOk(): bool
```

***

### isErr

Returns `true` if the result is an `err` result.

```php
public isErr(): bool
```

***

### getOk

Returns `Some(T)` if `ok`, or `None` if `err`

```php
public getOk(): \Ciarancoza\OptionResult\Option<\Ciarancoza\OptionResult\T>
```

***

### getErr

Returns `Some(E)` if `err`, or `None` if `ok`

```php
public getErr(): \Ciarancoza\OptionResult\Option<\Ciarancoza\OptionResult\E>
```

***

### unwrap

Returns the contained value if `ok`, otherwise throws UnwrapErrException

```php
public unwrap(): \Ciarancoza\OptionResult\T
```

**Return Value:**

The contained value

**Throws:**

- [`UnwrapErrException`](./Exceptions/UnwrapErrException)

***

### unwrapErr

Returns the contained value if `err`, otherwise throws UnwrapOkException

```php
public unwrapErr(): \Ciarancoza\OptionResult\E
```

**Return Value:**

The contained error value

**Throws:**

- [`UnwrapOkException`](./Exceptions/UnwrapOkException)

***

### unwrapOr

Returns the contained `ok` value or a provided default.

```php
public unwrapOr(\Ciarancoza\OptionResult\V $or): \Ciarancoza\OptionResult\T|\Ciarancoza\OptionResult\V
```

**Parameters:**

| Parameter | Type                           | Description |
|-----------|--------------------------------|-------------|
| `$or`     | **\Ciarancoza\OptionResult\V** |             |

***

### unwrapOrElse

Returns the contained `ok` value or computes from closure with error value

```php
public unwrapOrElse(callable $fn): \Ciarancoza\OptionResult\T|\Ciarancoza\OptionResult\V
```

**Parameters:**

| Parameter | Type         | Description |
|-----------|--------------|-------------|
| `$fn`     | **callable** |             |

***

### map

If `ok`, transform the value with `$fn`

```php
public map(callable $fn): \Ciarancoza\OptionResult\Result<\Ciarancoza\OptionResult\U,\Ciarancoza\OptionResult\E>
```

**Parameters:**

| Parameter | Type         | Description                     |
|-----------|--------------|---------------------------------|
| `$fn`     | **callable** | Function to transform the value |

***

### mapErr

If `err`, transform the error value with `$fn`

```php
public mapErr(callable $fn): \Ciarancoza\OptionResult\Result<\Ciarancoza\OptionResult\T,\Ciarancoza\OptionResult\U>
```

**Parameters:**

| Parameter | Type         | Description                     |
|-----------|--------------|---------------------------------|
| `$fn`     | **callable** | Function to transform the value |

***
