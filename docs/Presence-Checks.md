# Presence Checks

Use the `has*` methods to test whether a key exists in a source without
reading its value.

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

`hasGet()` answers *"is the key there?"*, not *"does it hold a useful
value?"*. Use it to tell an **absent** field apart from one submitted
**empty** — the classic unchecked vs. empty checkbox problem:

```php
$input = new Inputs(get: ['newsletter' => '']);

$input->hasGet('newsletter');     // true — the field was submitted
$input->get('newsletter') === ''; // true — but it is empty
```

When you need a non-empty value, combine a presence check with a value read
or, better, validate with a `required` rule:

```php
$name = $input->post('name', null, ['required']); // null when missing OR empty
```

## Keys are case-sensitive

```php
$input = new Inputs(get: ['Token' => 'abc']);

$input->hasGet('Token'); // true
$input->hasGet('token'); // false — different key
```

## Expected output

| Call | Result |
| --- | --- |
| `hasGet('note')` | `true` |
| `hasGet('email')` | `false` |
| `hasRaw('token')` | `true` |

## Common mistakes

- **Assuming case-insensitivity.** `hasGet('Debug')` is `false` when the
  stored key is `debug`. (1.x folded keys to lower-case; 2.0 does not — see
  [Migration from 1.x](Migration-from-1.x.md).)
- **Using presence as a truthiness test.** A present key can still be an
  empty string.

## Next

- [The Facade](The-Facade.md)
- [Reading Sources](Reading-Sources.md)
