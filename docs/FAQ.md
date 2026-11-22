# FAQ

Common questions and pitfalls. See also [Migration from 1.x](Migration-from-1.x.md).

## Are keys case-insensitive?

No. `get('name')` and `get('Name')` are different keys, and
`hasGet('Name')` is `false` when the stored key is `name`. This mirrors how
HTTP query and body parameters actually behave.

> Version 1.x folded keys to lower-case. 2.0 matches them exactly. If you
> relied on the old behaviour, normalise the key yourself:
> `get(strtolower($key))`.

## Why did `get()` return a string for a number?

Query and form values are always strings (`'2'`, not `2`) — that is what
PHP's SAPI provides. JSON-body values keep their decoded type. Cast
explicitly, or validate with a rule like `['integer']` and convert after:

```php
$page = (int) $input->get('page', 1, ['integer']);
```

## In a priority helper, why didn't it use the valid value from the next source?

Priority is decided by **presence**, not validity. The first source that
*contains* the key is committed to. If that value fails validation you get
the default — the helper does **not** roll over to the next source.

```php
$input = new Inputs(
    get:  ['year' => '3000'], // present but invalid
    post: ['year' => '1999'], // valid, never reached
);
$input->getPost('year', 2015, ['range(1970...2070)']); // 2015
```

If you need the next source's value, read the sources separately, or order
the helper so the trusted source comes first. See
[Source Priority](Source-Priority.md) and [Validation](Validation.md).

## My JSON body isn't showing up in `raw()`. Why?

Three common causes:

1. **The body was already read.** `php://input` can usually be read only
   once per request. Build `Inputs` once (or use the facade) and reuse it;
   or decode the body yourself and pass it via `new Inputs(raw: ...)`.
2. **The payload is a scalar or invalid JSON.** Only a JSON object or array
   becomes data; `42`, `"x"`, `true` or malformed JSON decode to an empty
   set by design.
3. **Nested access.** Each source is a *flat* bag — `raw('user.name')`
   looks up the literal key `user.name`, it does not descend. Read the
   nested array and index it: `$input->raw('user')['name'] ?? null`.

## Does the facade share data with a `new Inputs()` I create?

No. They are independent objects. The facade caches its own instance; your
`new Inputs()` reads its own sources. To put specific data behind the
facade, use `Facade\Inputs::setInstance(new Inputs(...))`. See
[The Facade](The-Facade.md).

## How do I test code that uses the facade?

Inject a seeded instance and reset afterwards:

```php
use InitPHP\Input\Facade\Inputs as Input;
use InitPHP\Input\Inputs;

Input::setInstance(new Inputs(get: ['name' => 'Jane']));
// ... exercise the code under test ...
Input::reset(); // in tearDown()
```

See [Testing](Testing.md).

## Can I use my own validator configuration?

Yes. Pass a preconfigured `InitPHP\Validation\Validation` as the fourth
constructor argument — for example one with a custom locale or `extend()`-ed
rules — and it is used for every validated lookup:

```php
$validator = (new \InitPHP\Validation\Validation())
    ->extend('even', static fn ($v): bool => is_numeric($v) && (int) $v % 2 === 0);

$input = new Inputs(get: ['n' => '4'], validation: $validator);
$input->get('n', null, ['even']); // '4'
```

## Can I read all values at once?

Not through this package — it is deliberately a *single value* reader.
If you need the whole array, read the source you want directly (`$_GET`,
`$_POST`, or the decoded body) or wrap it in
[`initphp/parameterbag`](https://github.com/InitPHP/ParameterBag).

## Does it support nested / dotted keys?

No. Sources are stored as flat bags, so `get('a.b')` is a lookup for the
literal key `a.b`. For nested structures, read the parent and index into
it, or use [`initphp/parameterbag`](https://github.com/InitPHP/ParameterBag)
in multi mode.

## Which PHP versions are supported?

PHP 8.2 and later. The package is tested on 8.2–8.5 in CI.

## Where do I report a bug or request a feature?

[Open an issue](https://github.com/InitPHP/Input/issues/new) on the
repository, or start a thread in
[Discussions](https://github.com/orgs/InitPHP/discussions).

## Next

- [API Reference](API-Reference.md)
- [Migration from 1.x](Migration-from-1.x.md)
