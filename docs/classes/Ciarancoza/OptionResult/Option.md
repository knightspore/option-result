
Option<T> represents an optional value.

An option may be `some` or `none`, where `some` contains a value and `none` does not.

***

* Full name: `\Ciarancoza\OptionResult\Option`

## Properties

### value

```php
private mixed $value
```

***

### isSome

```php
private bool $isSome
```

***

## Methods

### __construct

```php
private __construct(\Ciarancoza\OptionResult\T $value, bool $isSome): mixed
```

**Parameters:**

| Parameter | Type                           | Description |
|-----------|--------------------------------|-------------|
| `$value`  | **\Ciarancoza\OptionResult\T** |             |
| `$isSome` | **bool**                       |             |

***

### and

Returns `None` if the option is `None`, otherwise returns `$optb`

```php
public and(\Ciarancoza\OptionResult\Option<\Ciarancoza\OptionResult\U> $optb): \Ciarancoza\OptionResult\Option<\Ciarancoza\OptionResult\U>
```

**Parameters:**

| Parameter | Type                                                            | Description |
|-----------|-----------------------------------------------------------------|-------------|
| `$optb`   | **\Ciarancoza\OptionResult\Option<\Ciarancoza\OptionResult\U>** |             |

***

### andThen

Returns `None` if the option is `None`, otherwise calls `$f` with the wrapped value and returns the result.

```php
public andThen(callable $f): \Ciarancoza\OptionResult\Option<\Ciarancoza\OptionResult\U>
```

**Parameters:**

| Parameter | Type         | Description |
|-----------|--------------|-------------|
| `$f`      | **callable** |             |

***

### expect

Returns the contained `Some` value, or throws UnwrapNoneException if the value is `None` with a custom panic message provided by `$msg`.

```php
public expect(string $msg): \Ciarancoza\OptionResult\T
```

**Parameters:**

| Parameter | Type       | Description |
|-----------|------------|-------------|
| `$msg`    | **string** |             |

**Throws:**

- [`UnwrapNoneException`](./Exceptions/UnwrapNoneException)

***

### filter

Returns `None` if the option is `None`, otherwise calls `$predicate` with the wrapped value and returns:
- `Some(T)` if `predicate` returns `true` (where `t` is the wrapped value and
- `None` if `predicate` returns `false`

```php
public filter(callable $predicate): \Ciarancoza\OptionResult\Option<\Ciarancoza\OptionResult\T>
```

**Parameters:**

| Parameter    | Type         | Description |
|--------------|--------------|-------------|
| `$predicate` | **callable** |             |

***

### inspect

Calls a function with a reference to the contained value if `Some`

```php
public inspect(callable $f): \Ciarancoza\OptionResult\Option<\Ciarancoza\OptionResult\T>
```

**Parameters:**

| Parameter | Type         | Description |
|-----------|--------------|-------------|
| `$f`      | **callable** |             |

***

### isNone

Returns `true` of the option is a `None` value

```php
public isNone(): bool
```

***

### isSome

Returns `true` of the option is a `Some` value

```php
public isSome(): bool
```

***

### map

Maps an `Option<T>` to `Option<U>` by applying a function to a contained value (if `Some`) or returns `None` (if `None`)

```php
public map(callable $f): \Ciarancoza\OptionResult\Option<\Ciarancoza\OptionResult\U>
```

**Parameters:**

| Parameter | Type         | Description |
|-----------|--------------|-------------|
| `$f`      | **callable** |             |

***

### mapOr

Returns the provided default (if none), or applies a function to the contained value (if any).

```php
public mapOr(\Ciarancoza\OptionResult\V $or, callable $f): \Ciarancoza\OptionResult\U|\Ciarancoza\OptionResult\V
```

**Parameters:**

| Parameter | Type                           | Description |
|-----------|--------------------------------|-------------|
| `$or`     | **\Ciarancoza\OptionResult\V** |             |
| `$f`      | **callable**                   |             |

***

### None

Creates a `none` Option

```php
public static None(): \Ciarancoza\OptionResult\Option<never>
```

* This method is **static**.
***

### reduce

Reduces two options into one, using the provided function if both are `Some`.

```php
public reduce(\Ciarancoza\OptionResult\Option<\Ciarancoza\OptionResult\V> $other, callable $f): \Ciarancoza\OptionResult\Option<\Ciarancoza\OptionResult\U|\Ciarancoza\OptionResult\T|\Ciarancoza\OptionResult\V>
```

If `$this` is `Some(s)` and `$other` is `Some(o)`, this method returns `Some($f(s,o))`.
Otherwise, if only one of `$this` and `$other` is `Some`, that one is returned.
If both `$this` and `$other` are `None`, `None` is returned.

**Parameters:**

| Parameter | Type                                                            | Description |
|-----------|-----------------------------------------------------------------|-------------|
| `$other`  | **\Ciarancoza\OptionResult\Option<\Ciarancoza\OptionResult\V>** |             |
| `$f`      | **callable**                                                    |             |

***

### Some

Creates a `some` Option

```php
public static Some(\Ciarancoza\OptionResult\T $value = true): \Ciarancoza\OptionResult\Option<\Ciarancoza\OptionResult\T>
```

* This method is **static**.
**Parameters:**

| Parameter | Type                           | Description |
|-----------|--------------------------------|-------------|
| `$value`  | **\Ciarancoza\OptionResult\T** |             |

***

### unwrap

Returns the contained `Some` value or throws UnwrapNoneException

```php
public unwrap(): \Ciarancoza\OptionResult\T
```

**Throws:**

- [`UnwrapNoneException`](./Exceptions/UnwrapNoneException)

***

### unwrapOr

Returns the contained `some` value or a provided default.

```php
public unwrapOr(\Ciarancoza\OptionResult\V $or): \Ciarancoza\OptionResult\T|\Ciarancoza\OptionResult\V
```

**Parameters:**

| Parameter | Type                           | Description |
|-----------|--------------------------------|-------------|
| `$or`     | **\Ciarancoza\OptionResult\V** |             |

***
