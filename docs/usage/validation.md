# Validation

**Goal:** accept a value only when it satisfies a set of rules, and fall
back to a default otherwise.

Every accessor takes an optional **third argument**: a list of validation
rules. The list is passed straight to
[initphp/validation](https://github.com/InitPHP/Validation), so any rule
that package understands is available here.

```php
public function get(string $key, mixed $default = null, ?array $validation = null): mixed;
```

When the list is empty or `null`, no validation runs.

## Working example

```php
use InitPHP\Input\Inputs;

$input = new Inputs(get: ['year' => '1999']);

// Accept only a year between 1970 and 2070, else 2015.
$input->get('year', 2015, ['range(1970...2070)']); // '1999'
```

```php
$input = new Inputs(get: ['year' => '3000']);

$input->get('year', 2015, ['range(1970...2070)']); // 2015 — out of range
```

## Rules are a list

Each entry is either a rule string or a callable. Strings may chain
several checks; you can also pass several entries:

```php
$input = new Inputs(post: [
    'password'        => 's3cret',
    'password_retype' => 's3cret',
]);

$input->post('password', null, ['required', 'again(password_retype)']);
// 's3cret' — present and equal to password_retype
```

Cross-field rules such as `again(password_retype)` work because the whole
source bag is handed to the validator, so sibling fields resolve.

### Callable rules

```php
$even = static fn ($value): bool => is_numeric($value) && (int) $value % 2 === 0;

$input = new Inputs(get: ['n' => '20']);
$input->get('n', null, [$even]); // '20'
```

## Priority + validation: no fall-through

The first source that **owns** the key is the one validated. A present
but invalid value returns the default; it does **not** roll over to the
next source.

```php
$input = new Inputs(
    get:  ['year' => '3000'], // present but invalid
    post: ['year' => '1999'], // valid, but never reached
);

$input->getPost('year', 2015, ['range(1970...2070)']); // 2015
```

If you want the valid POST value here, read the sources separately and
decide yourself, or order the helper so the source you trust comes first.

## Expected output

| Scenario                                   | Result   |
|--------------------------------------------|----------|
| `get('year','d',['range(1970...2070)'])` with `1999` | `'1999'` |
| same with `3000`                           | `'d'`    |
| `post('password',…,['required','again(password_retype)'])` matching | the value |
| same, mismatched retype                    | the default |

## Common mistakes

- **Passing a rule string instead of a list.** The argument is an
  **array**: `['required']`, not `'required'`.
- **Expecting fall-through on invalid.** It does not happen — see above.
- **Forgetting cross-field data.** `again(other)` needs `other` to exist
  in the *same* source you are reading from.

## Next

- [Presence checks](presence-checks.md)
- [Source priority](source-priority.md)
