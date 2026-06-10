# Presence checks

**Goal:** test whether a key exists in a source without reading its
value.

```php
public function hasGet(string $key): bool;
public function hasPost(string $key): bool;
public function hasRaw(string $key): bool;
```

Each is the equivalent of `array_key_exists()` against its source — and,
like `array_key_exists()`, a key whose value is `null` still counts as
present.

## Working example

```php
use InitPHP\Input\Inputs;

$input = new Inputs(
    get:  ['debug' => '1', 'note' => null],
    post: ['email' => 'jane@example.com'],
    raw:  ['token' => 'abc'],
);

$input->hasGet('debug');  // true
$input->hasGet('note');   // true  — present even though the value is null
$input->hasGet('email');  // false — that key is in post, not get

$input->hasPost('email'); // true
$input->hasRaw('token');  // true
$input->hasRaw('debug');  // false
```

## Presence vs. truthiness

`hasGet()` answers "is the key there?", not "does it hold a useful
value?". Use it to distinguish an absent field from one submitted empty:

```php
$input = new Inputs(get: ['newsletter' => '']);

$input->hasGet('newsletter');        // true — the checkbox was submitted
$input->get('newsletter') === '';    // true — but it is empty
```

## Expected output

| Call                | Result  |
|---------------------|---------|
| `hasGet('note')`    | `true`  |
| `hasGet('email')`   | `false` |
| `hasRaw('token')`   | `true`  |

## Common mistakes

- **Assuming case-insensitivity.** `hasGet('Debug')` is `false` when the
  key is `debug`. Keys are case-sensitive.
- **Using presence as a truthiness test.** A present key can still be an
  empty string. Combine with a value read or a `required` rule when you
  need a non-empty value.

## Next

- [The facade](facade.md)
- [Reading sources](reading-sources.md)
