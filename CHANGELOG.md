# Changelog

## v1.2.4
- `UnwrapErrException` and `UnwrapOkException` append scalar, `Stringable` and `Throwable` values to the default message, so the reason shows in logs and error trackers without inspecting `->value`.

## v1.2.3
- Precise generic types: `and`/`andThen` return `Result<U, E|F>`, `or` returns `Result<T|U, F>`, `tryCatch` has a conditional return type, and `Option::Some`/`Option::map` strip `null` from the inferred type to match the "Some(null) is None" rule.
- PHPStan `assertType` checks under `tests/Types`, run by `composer analyse`.

## v1.2.2
- CI runs PHP 8.1 through 8.4 and `composer analyse`.
- README section on using the package with Laravel.

## v1.2.1
- **Behaviour change:** `Option` and `Result` are `final`. Constructors were already private, so no working subclass could have existed.
- Static analysis clean at PHPStan level max; covariant templates; factories return `self`.

## v1.2.0
- `UnwrapErrException` and `UnwrapOkException` expose the contained value as `->value`. A `Throwable` `Err` value is chained as the previous exception.

## v1.1.0
- **Behaviour change:** only `Closure` instances are treated as lazy defaults in `unwrapOr`, `mapOr`, `okOr` and `or`. Function-name strings, `[Class, 'method']` arrays and invokable objects are now returned as values. Previously `Option::None()->unwrapOr('phpversion')` returned the PHP version.

## v1.0.1
- Initial stable release.
