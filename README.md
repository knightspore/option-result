# option-result - Rust-style `Option` and `Result` Classes for PHP

> ["So you can take down ~20% of the internet, but with PHP"](https://blog.cloudflare.com/18-november-2025-outage/#memory-preallocation)

This library contains two classes: `Option` and `Result`.

`Option<T>` represents an optional value. An option may be `some` or `none`, where `some(T)` contains a value and `none` does not.

`Result<T,E>` represents a success (`ok(T)`) or an error (`err(E)`).

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
        ->map(fn ($org) => $org->getEmails())
        ->map(fn ($emails) => $emails['active'])
        ->mapErr(fn ($error) => "Failed to get active emails: " . $error)
        ->mapErr(fn ($error) => Log::error($error))
        ->unwrapOr([]);
}
```

You can view [the generated documentation](https://github.com/knightspore/option-result/tree/main/docs) for more usage details.

## Road Map

- Add useful methods
    - Option
        - [ ] `filter()`
        - [ ] `inspect()`
        - [ ] `reduce()`
        - [ ] `replace()`
        - [ ] `take()`
        - [ ] `takeIf()`
        - [x] `None()`
        - [x] `Some()`
        - [x] `and()`
        - [x] `andThen()`
        - [x] `expect()`
        - [x] `isNone()`
        - [x] `isSome()`
        - [x] `map()`
        - [x] `mapOr()`
        - [x] `unwrap()`
        - [x] `unwrapOr()`
    - Result
        - [ ] `inspect()`
        - [ ] `inspectErr()`
        - [ ] `or()`
        - [ ] `tryCatch()`
        - [x] `Err()`
        - [x] `Ok()`
        - [x] `and()`
        - [x] `andThen()`
        - [x] `expect()`
        - [x] `expectErr()`
        - [x] `getErr()`
        - [x] `getOk()`
        - [x] `isErr()`
        - [x] `isOk()`
        - [x] `map()`
        - [x] `mapErr()`
        - [x] `mapOr()` 
        - [x] `unwrap()`
        - [x] `unwrapErr()`
        - [x] `unwrapOr(mixed $default)`
- Refactor
    - Consider move to Interface+Enum for easier instantiation
    - Use clear if statements everywhere match doesn't make sense
    - Review compared to rust types
        - Input / Output types
        - Type Docs 

## Contributing

Go for it! There are plenty of useful Option / Result features in Rust we could implement in this library.

