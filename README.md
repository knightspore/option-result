# option-result - Rust-style `Option` and `Result` Classes for PHP

> ["So you can take down ~20% of the internet, but with PHP"](https://blog.cloudflare.com/18-november-2025-outage/#memory-preallocation)

This library contains two classes: `Option` and `Result`.

`Option<T>` represents an optional value. An option may be `Some` or `None`, where `Some(T)` contains a value and `None` does not.

`Result<T,E>` represents a success (`Ok(T)`) or an error (`Err(E)`).

Both are ported from the Rust standard library, with a few deliberate changes for PHP:

- Wherever Rust has an eager method and a lazy `*Else` twin that takes a zero-argument closure (`unwrap_or` / `unwrap_or_else`, `or` / `or_else`, `ok_or` / `ok_or_else`), there is a single method that accepts either a plain value or a `Closure`. Only `Closure` instances are treated as lazy: strings, `[Class, 'method']` arrays and invokable objects are returned as-is, so a default that happens to name a function is never called.
- Where the lazy twin's closure receives the error (`Result::unwrap_or_else`, `Result::map_or_else`), it is kept as a separate method so the error is available.
- `Option::Some(null)` is `None`. `Result::Ok(null)` is still `Ok`.
- In-place mutators (`take`, `replace`, `insert`, …) are not ported. PHP objects are shared references, so mutating one in place is a footgun.
- `Result::tryCatch()` is a PHP addition that turns a throwing call into a `Result`.

## Installation

```bash
composer require ciarancoza/option-result
```

## Usage

### Option

```php
function findUser(int $id): Option {
    $user = User::find($id);
    return $user ? Option::Some($user) : Option::None();
}

function getUserTheme(int $userId): string {
    return findUser($userId)
        ->map(fn ($user) => $user->theme)
        ->map(fn ($theme) => strtolower($theme))
        ->unwrapOr('auto');
}
```

### Result

```php
function fetchOrgData(int $id): Result {
    try {
        $response = Http::get("/api/orgs/{$id}");
        if ($response->failed()) return Result::Err("API request failed: " . $response->status());
        return Result::Ok($response->json());
    } catch (Exception $e) {
        return Result::Err("Connection error: " . $e->getMessage());
    }
}

function getActiveEmails(int $orgId): array {
    return fetchOrgData($orgId)
        ->map(fn ($org) => $org['emails'])
        ->map(fn ($emails) => $emails['active'])
        ->mapErr(fn ($error) => "Failed to get active emails: " . $error)
        ->inspectErr(fn ($error) => Log::error($error))
        ->unwrapOr([]);
}
```

### Wrapping exceptions with `tryCatch`

```php
$config = Result::tryCatch(fn () => json_decode(file_get_contents($path), flags: JSON_THROW_ON_ERROR))
    ->mapErr(fn (Throwable $e) => "Could not load config: " . $e->getMessage())
    ->unwrapOr([]);

// Or map the Throwable to your own error type in the second argument
$user = Result::tryCatch(fn () => User::findOrFail($id), fn (Throwable $e) => UserError::NotFound);
```

### Converting between `Option` and `Result`

```php
$result = findUser($id)->okOr("User {$id} not found");  // Option<User>  -> Result<User, string>
$option = fetchOrgData($id)->getOk();                    // Result<T, E>  -> Option<T>
$error  = fetchOrgData($id)->getErr();                   // Result<T, E>  -> Option<E>
```

You can view [the generated documentation](https://github.com/knightspore/option-result/tree/main/docs) for more usage details.

## Using with Laravel

The package has no framework dependencies and works as-is. A few things to keep in mind, because Laravel's own control flow is exception-driven:

- **`Err` is a value, not an exception.** `DB::transaction()` will commit and a queued job will be marked successful if your code returns `Err` without throwing. Unwrap or rethrow at the boundary where you want the framework to react, e.g. `->unwrapOrElse(fn ($e) => throw $e)`.
- **`tryCatch` catches every `Throwable`**, including `TypeError` and `ValidationException`. Pass a `$catch` that maps the ones you own and rethrows the rest, or scope the closure tightly.
- **`Option::Some(null)` is `None`.** Mapping onto a nullable column (`->map(fn ($u) => $u->theme)`) will turn `Some` into `None` mid-chain. That is usually what you want; if not, map to a sentinel first.
- **Name collisions.** `vlucas/phpdotenv` ships `PhpOption\Option` and `GrahamCampbell\ResultType\Result`, so IDE auto-import may offer the wrong class. Alias on import if both are in scope.
- **Not serialisable by design.** Neither class implements `JsonSerializable`; unwrap before returning from a controller or putting a value in a job payload or cache.

## Implemented Methods

| Option | Result |
|---|---|
| `Some(T)`, `None()` | `Ok(T)`, `Err(E)`, `tryCatch(callable, ?callable)` |
| `isSome()`, `isNone()` | `isOk()`, `isErr()` |
| `unwrap()`, `unwrapOr(T\|callable)`, `expect(string)` | `unwrap()`, `unwrapOr(T\|callable)`, `unwrapOrElse(callable(E))`, `expect(string)` |
| | `unwrapErr()`, `expectErr(string)` |
| `map(callable)`, `mapOr(T\|callable, callable)` | `map(callable)`, `mapOr(T\|callable, callable)`, `mapOrElse(callable(E), callable)`, `mapErr(callable)` |
| `and(Option)`, `andThen(callable)` | `and(Result)`, `andThen(callable)` |
| `or(Option\|callable)` | `or(Result\|callable(E))` |
| `filter(callable)`, `reduce(Option, callable)` | |
| `inspect(callable)` | `inspect(callable)`, `inspectErr(callable)` |
| `okOr(E\|callable)` | `getOk()`, `getErr()` |

## Development

```bash
composer test     # phpunit
composer lint     # pint
composer analyse  # phpstan, level max
composer docs     # regenerate docs/ with phpDocumentor
composer release  # all of the above
```

## Contributing

Go for it! There are plenty of useful Option / Result features in Rust we could implement in this library.
