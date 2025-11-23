
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

### None

Creates a `none` Option

```php
public static None(): \Ciarancoza\OptionResult\Option<never>
```

* This method is **static**.
***

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

### isSome

Returns `true` if the option is a `some` option.

```php
public isSome(): bool
```

***

### isNone

Returns `true` if the option is a `none` option.

```php
public isNone(): bool
```

***

### unwrap

Returns the contained value if `some`, otherwise throws UnwrapNoneException.

```php
public unwrap(): \Ciarancoza\OptionResult\T
```

**Return Value:**

The contained value

**Throws:**

When called on `None`
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

### map

Calls `fn` on contained value if `some`, returns `none` if `none`

```php
public map(callable $fn): \Ciarancoza\OptionResult\Option<\Ciarancoza\OptionResult\U>
```

**Parameters:**

| Parameter | Type         | Description                     |
|-----------|--------------|---------------------------------|
| `$fn`     | **callable** | Function to transform the value |

***
