# FAQ

Common pitfalls and clarifications.

## Are keys case-insensitive?

No. `get('name')` and `get('Name')` are different keys, and
`hasGet('Name')` is `false` when the stored key is `name`. This mirrors
how HTTP query and body parameters actually behave.

> Earlier 1.x releases folded keys to lower-case. Version 2 matches keys
> exactly. If you relied on the old behaviour, normalise the key yourself
> before calling, e.g. `get(strtolower($key))`.

## Why did `get()` return a string for a number?

Query and form values are always strings (`'2'`, not `2`) — that is what
the SAPI provides. JSON-body values keep their decoded type. Cast
explicitly, or validate with a rule like `['integer']` and convert after.

## In a priority helper, why didn't it use the valid value from the next source?

Priority is decided by **presence**, not validity. The first source that
*contains* the key is committed to. If that value fails validation you
get the default — the helper does not roll over to the next source.

```php
$input = new Inputs(
    get:  ['year' => '3000'], // present but invalid
    post: ['year' => '1999'], // valid, never reached
);
$input->getPost('year', 2015, ['range(1970...2070)']); // 2015
```

If you need the next source's value, read the sources separately, or
order the helper so the trusted source comes first.

## My JSON body isn't showing up in `raw()`. Why?

Three common causes:

1. **The body was already read.** `php://input` can usually be read only
   once per request. Build `Inputs` once (or use the facade) and reuse
   it; or decode the body yourself and pass it via `new Inputs(raw: ...)`.
2. **The payload is a scalar or invalid JSON.** Only a JSON object or
   array becomes data; `42`, `"x"`, `true` or malformed JSON decode to an
   empty set by design.
3. **Nested access.** Each source is a *flat* bag — `raw('user.name')`
   looks up the literal key `user.name`, it does not descend. Read the
   nested array and index it: `$input->raw('user')['name'] ?? null`.

## Does the facade share data with a `new Inputs()` I create?

No. They are independent objects. The facade caches its own instance;
your `new Inputs()` reads its own sources. To put specific data behind
the facade, use `Facade\Inputs::setInstance(new Inputs(...))`.

## How do I test code that uses the facade?

Inject a seeded instance and reset afterwards:

```php
use InitPHP\Input\Facade\Inputs as Input;
use InitPHP\Input\Inputs;

Input::setInstance(new Inputs(get: ['name' => 'Jane']));
// ... exercise the code under test ...
Input::reset(); // in tearDown()
```

## Can I use my own validator configuration?

Yes. Pass a preconfigured `InitPHP\Validation\Validation` as the fourth
constructor argument — for example one with a custom locale or
`extend()`-ed rules — and it will be used for every validated lookup:

```php
$validator = (new \InitPHP\Validation\Validation())
    ->extend('even', static fn ($v): bool => is_numeric($v) && (int) $v % 2 === 0);

$input = new Inputs(get: ['n' => '4'], validation: $validator);
$input->get('n', null, ['even']); // '4'
```

## Which PHP versions are supported?

PHP 8.1 and later. The package is checked on 8.1–8.4 in CI.
