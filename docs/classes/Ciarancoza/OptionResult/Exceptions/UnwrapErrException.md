
***

* Full name: `\Ciarancoza\OptionResult\Exceptions\UnwrapErrException`
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
public __construct(string $message = 'Attempted to call `unwrap()` on `Err` value', mixed $value = null): mixed
```

**Parameters:**

| Parameter  | Type       | Description                                                                                  |
|------------|------------|----------------------------------------------------------------------------------------------|
| `$message` | **string** |                                                                                              |
| `$value`   | **mixed**  | The contained `Err` value. If it is a `Throwable`, it is also set as the previous exception. |

***
