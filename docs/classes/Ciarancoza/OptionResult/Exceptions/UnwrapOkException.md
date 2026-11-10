
***

* Full name: `\Ciarancoza\OptionResult\Exceptions\UnwrapOkException`
* Parent class: [`RuntimeException`](../../../RuntimeException)

## Properties

### value

```php
public mixed $value
```

***

## Methods

### __construct

```php
public __construct(?string $message = null, mixed $value = null): mixed
```

**Parameters:**

| Parameter  | Type        | Description               |
|------------|-------------|---------------------------|
| `$message` | **?string** |                           |
| `$value`   | **mixed**   | The contained `Ok` value. |

***

### describe

Appends a scalar or Stringable value to the default message so the reason is visible in logs and error trackers.

```php
private static describe(mixed $value): string
```

* This method is **static**.
**Parameters:**

| Parameter | Type      | Description |
|-----------|-----------|-------------|
| `$value`  | **mixed** |             |

***
