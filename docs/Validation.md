# Validation

Every accessor takes an optional **third argument**: a list of validation
rules. When the resolved value fails them, the accessor returns the default
instead. Validation is powered by
[initphp/validation](https://github.com/InitPHP/Validation), so any rule
that package understands is available here.

```php
public function get(string $key, mixed $default = null, ?array $validation = null): mixed;
```

When the list is empty (`[]`) or `null`, no validation runs.

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

Each entry is either a **rule string** or a **callable**. A rule string can
chain several checks with `|`, and you can pass several entries:

```php
$input = new Inputs(post: [
    'password'        => 's3cret',
    'password_retype' => 's3cret',
]);

$input->post('password', null, ['required', 'again(password_retype)']);
// 's3cret' — present and equal to password_retype
```

A few rules you will reach for often:

| Rule | Passes when the value… |
| --- | --- |
| `required` | is present and not empty |
| `integer` | is an int or an int-looking string (`'42'`) |
| `mail` | is a valid e-mail address |
| `range(1...10)` | is a number within the bounds |
| `length(3...20)` | has a length within the bounds |
| `again(other)` | equals the `other` field in the same source |
| `only(a, b, c)` | is one of the listed values |

The full catalogue lives in the Validation wiki's
[Rules Reference](https://github.com/InitPHP/Validation/wiki/Rules-Reference).

### Cross-field rules

Rules such as `again(password_retype)` work because the **whole source
bag** is handed to the validator, so sibling fields resolve. The sibling
must live in the *same* source you are reading from:

```php
// both fields come from $_POST, so again() can see password_retype
$input->post('password', null, ['required', 'again(password_retype)']);
```

### Callable rules

Pass a closure for a one-off check. It receives the value and returns a
boolean:

```php
$even = static fn ($value): bool => is_numeric($value) && (int) $value % 2 === 0;

$input = new Inputs(get: ['n' => '20']);
$input->get('n', null, [$even]); // '20'
```

## Priority + validation: no fall-through

In a [priority helper](Source-Priority.md), the first source that **owns** the
key is the one validated. A present-but-invalid value returns the default;
it does **not** roll over to the next source.

```php
$input = new Inputs(
    get:  ['year' => '3000'], // present but invalid
    post: ['year' => '1999'], // valid, but never reached
);

$input->getPost('year', 2015, ['range(1970...2070)']); // 2015
```

If you want the valid POST value here, read the sources separately:

```php
$year = 2015;
if ($input->hasGet('year') && $input->get('year', null, ['range(1970...2070)']) !== null) {
    $year = $input->get('year');
} elseif ($input->hasPost('year')) {
    $candidate = $input->post('year', null, ['range(1970...2070)']);
    if ($candidate !== null) {
        $year = $candidate;
    }
}
```

…or simply order the helper so the trusted source comes first.

## Bringing your own validator

The fourth constructor argument accepts a preconfigured
`InitPHP\Validation\Validation` — for example one with a custom locale or
`extend()`-ed rules. It is used for every validated lookup:

```php
use InitPHP\Input\Inputs;
use InitPHP\Validation\Validation;

$validator = (new Validation())
    ->extend('even', static fn ($v): bool => is_numeric($v) && (int) $v % 2 === 0);

$input = new Inputs(get: ['n' => '4'], validation: $validator);

$input->get('n', null, ['even']); // '4' — uses your custom rule
```

## Expected output

| Scenario | Result |
| --- | --- |
| `get('year', 2015, ['range(1970...2070)'])` with `1999` | `'1999'` |
| the same call with `3000` | `2015` |
| `post('password', null, ['required', 'again(password_retype)'])`, retype matches | the password |
| the same call, retype differs | `null` (the default) |

## Common mistakes

- **Passing a rule string instead of a list.** The argument is an
  **array**: `['required']`, not `'required'`.
- **Expecting fall-through on invalid.** It does not happen — see above.
- **Cross-field rule, wrong source.** `again(other)` needs `other` in the
  *same* source you are reading.

## Next

- [Source Priority](Source-Priority.md)
- [Recipes](Recipes.md)
- [Rules Reference (Validation wiki)](https://github.com/InitPHP/Validation/wiki/Rules-Reference)
