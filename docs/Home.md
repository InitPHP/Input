# InitPHP Input — Wiki

Welcome to the official documentation for **`initphp/input`** — a small,
focused library for reading a single request value from the query string,
the submitted form fields, or the JSON request body, with configurable
**source priority** and optional **validation**, behind one tidy API.

Built for **PHP 8.2+**.

```bash
composer require initphp/input
```

```php
use InitPHP\Input\Facade\Inputs as Input;

// GET /?page=2
$page  = Input::get('page', 1);                          // '2'
$year  = Input::getPost('year', 2015, ['range(1970...2070)']);
$token = Input::raw('token');                            // from the JSON body
```

## The package in one table

| Symbol | Role |
| --- | --- |
| [`Inputs`](API-Reference.md#class-inputs) | The implementation. Wraps the three sources and reads values from them. |
| [`InputInterface`](API-Reference.md#interface-inputinterface) | The contract every accessor implements — type-hint this in your services. |
| [`Facade\Inputs`](The-Facade.md) | A static proxy over a single `Inputs` instance: `Input::get(...)`. |
| [`Inputs::decodeJsonBody()`](API-Reference.md#decodejsonbody) | Safely turn a raw JSON body into an input array (scalars/garbage become `[]`). |

## Start here

- **New to the package?** Read [Installation](Installation.md), then
  [Quick Start](Quick-Start.md).
- **Reading one source?** [Reading Sources](Reading-Sources.md) covers
  `get`, `post` and `raw`.
- **Value might be in several places?** [Source Priority](Source-Priority.md)
  explains the twelve priority helpers and the *first source that has the
  key wins* rule.
- **Want to reject bad input?** [Validation](Validation.md).
- **Prefer static calls?** [The Facade](The-Facade.md).
- **Coming from 1.x?** Read [Migration from 1.x](Migration-from-1.x.md) — 2.0
  is a breaking release.

## What you get

- **Three sources behind one API.** `get` (`$_GET`), `post` (`$_POST`) and
  `raw` (the decoded JSON `php://input` body). See
  [Reading Sources](Reading-Sources.md).
- **Twelve priority helpers.** `getPost`, `postRawGet`, `rawGetPost`, …
  read the sources in a defined order and return the first one that
  contains the key. See [Source Priority](Source-Priority.md).
- **Per-call validation.** Hand a list of rules to any accessor; a value
  that fails yields the default. Powered by
  [initphp/validation](https://github.com/InitPHP/Validation). See
  [Validation](Validation.md).
- **A safe JSON body reader.** A scalar or malformed body decodes to an
  empty set instead of a fatal error.
- **A facade and an injectable instance.** Use `Input::get(...)` for app
  code, or `new Inputs(...)` with explicit data for tests and DI. See
  [The Facade](The-Facade.md) and [Testing](Testing.md).
- **No shared static state.** Two `Inputs` objects never interfere;
  PHPStan level max clean; 100% test coverage.

## The three sources at a glance

| Source | Backed by      | Accessor | Presence check |
| ------ | -------------- | -------- | -------------- |
| `get`  | `$_GET`        | `get()`  | `hasGet()`     |
| `post` | `$_POST`       | `post()` | `hasPost()`    |
| `raw`  | `php://input`* | `raw()`  | `hasRaw()`     |

\* The body is read once and JSON-decoded. Only a JSON object/array
becomes data; anything else is treated as empty.

## Package metadata

- **License**: MIT
- **Minimum PHP**: 8.2
- **Required extensions**: `ext-json`
- **Runtime dependencies**:
  [`initphp/parameterbag ^3.0`](https://github.com/InitPHP/ParameterBag),
  [`initphp/validation ^3.0`](https://github.com/InitPHP/Validation)
- **Packagist**: [`initphp/input`](https://packagist.org/packages/initphp/input)
- **Source**: [github.com/InitPHP/Input](https://github.com/InitPHP/Input)
- **Issues**: [github.com/InitPHP/Input/issues](https://github.com/InitPHP/Input/issues)
- **Discussions**: [github.com/orgs/InitPHP/discussions](https://github.com/orgs/InitPHP/discussions)

If something in this wiki is unclear, wrong, or out of date,
[open an issue](https://github.com/InitPHP/Input/issues/new) — documentation
fixes are merged eagerly.
