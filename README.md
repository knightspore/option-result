# OptionResult

Rust-style Option and Result Classes for PHP

## Installation

```bash
composer require --dev ciarancoza/option-result
```

## Usage

This library contains two classes: `Option<T>` and `Result<T,E>`.

Which is [appropriate for your use case]? 
- You have one failure mode: `Option`
- Two have two or more failure modes: `Result`

### Option

```php
function findUser(int $id): Option {
    $user = User::find($id);
    return $user ? Option::Some($user) : Option::None();
}

function getUserName(int $userId): string {
    return findUser($userId)
        ->map(fn($user) => $user->name)
        ->map(fn($name) => ucfirst($name))
        ->unwrapOr('Unknown User');
}

echo getUserName(123); // "John Doe" or "Unknown User"
```

### Result

```php
function fetchUserData(int $id): Result {
    try {
        $response = Http::get("/api/users/{$id}");
        
        if ($response->failed()) {
            return Result::Err("API request failed: " . $response->status());
        }
        
        return Result::Ok($response->json());
    } catch (Exception $e) {
        return Result::Err("Connection error: " . $e->getMessage());
    }
}

function processUser(int $userId): array {
    return fetchUserData($userId)
        ->map(fn($data) => $data['user'])
        ->map(fn($user) => [
            'id' => $user['id'],
            'name' => ucfirst($user['name']),
            'email' => strtolower($user['email'])
        ])
        ->mapErr(fn($error) => "Failed to process user: " . $error)
        ->unwrapOr(['error' => 'User not found']);
}

$userData = processUser(123);
// Either processed user data or error information
```

## Road Map

- Add useful methods
    - Option
        - [x] `map()`
        - [ ] `expect(string $message)`
        - [ ] `andThen(callback $fn)`
    - Result
        - [x] `unwrap()`
        - [x] `unwrapErr()`
        - [x] `unwrapOr(mixed $default)`
        - [x] `map()`
        - [x] `mapErr()`
        - [ ] `expect(string $message)`
        - [ ] `expectErr(string $message)`
        - [ ] `andThen(callback $fn)`
        - [ ] `safe(throwable $fn, callback $onMapErr)` could be cool

## Contributing

Go for it! There are plenty of useful Option / Result features in Rust we could implement in this library.

