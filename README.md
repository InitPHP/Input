# InitPHP Input

Read a single request value from the query string, the submitted form
fields or the JSON request body — with configurable source priority and
optional validation, behind one small API.

[![Latest Stable Version](https://poser.pugx.org/initphp/input/v)](https://packagist.org/packages/initphp/input)
[![Total Downloads](https://poser.pugx.org/initphp/input/downloads)](https://packagist.org/packages/initphp/input)
[![CI](https://github.com/InitPHP/Input/actions/workflows/ci.yml/badge.svg)](https://github.com/InitPHP/Input/actions/workflows/ci.yml)
[![License](https://poser.pugx.org/initphp/input/license)](https://packagist.org/packages/initphp/input)
[![PHP Version Require](https://poser.pugx.org/initphp/input/require/php)](https://packagist.org/packages/initphp/input)

---

## Features

- Three input sources behind one API: `get` (`$_GET`), `post` (`$_POST`)
  and `raw` (the decoded JSON `php://input` body).
- Twelve priority helpers (`getPost`, `postRawGet`, …) that read the
  sources in a defined order — **the first source that contains the key
  wins**.
- Per-call validation powered by
  [initphp/validation](https://github.com/InitPHP/Validation): a value
  that fails its rules yields the default.
- A safe JSON body reader: a scalar or malformed payload becomes an empty
  set instead of a fatal error.
- A static facade for ergonomic access, plus a fully injectable instance
  for testing and dependency injection.
- No shared static state between instances; PHPStan level max clean.

## Requirements

- PHP 8.1 or later
- [initphp/parameterbag](https://github.com/InitPHP/ParameterBag) `^2.0`
- [initphp/validation](https://github.com/InitPHP/Validation) `^2.0`
- `ext-json`

## Installation

```bash
composer require initphp/input
```

## Quick start

### With the facade

```php
require_once 'vendor/autoload.php';

use InitPHP\Input\Facade\Inputs as Input;

// GET /?name=Jane
// echo isset($_GET['name']) ? $_GET['name'] : 'John';
echo Input::get('name', 'John'); // 'Jane'
```

### With an instance

```php
use InitPHP\Input\Inputs;

$input = new Inputs(); // reads $_GET, $_POST and php://input

$name = $input->get('name', 'John');
```

You can also hand the sources in explicitly — handy in tests or when the
data does not come from the superglobals:

```php
$input = new Inputs(
    get: ['name' => 'Jane'],
    post: ['email' => 'jane@example.com'],
    raw: ['token' => 'abc123'],
);
```

## Reading from a single source

```php
$input->get('name', 'guest');   // from $_GET
$input->post('email');          // from $_POST
$input->raw('token');           // from the JSON request body
```

Each accessor returns the default (second argument, `null` when omitted)
if the key is absent. Keys are matched **case-sensitively**, just like
real HTTP query and body parameters.

## Source priority

The priority helpers walk their sources in the order their name reads,
left to right, and return the value of the **first source that contains
the key**:

```php
// GET /?year=1999  (no POST, no body)
$input->getPost('year', 2015); // 1999 — taken from $_GET

// POST year=1999  (no GET)
$input->getPost('year', 2015); // 1999 — fell through to $_POST
```

The full set: `getPost`, `getRaw`, `getPostRaw`, `getRawPost`, `postGet`,
`postRaw`, `postGetRaw`, `postRawGet`, `rawGet`, `rawPost`, `rawGetPost`,
`rawPostGet`.

## Validation

Pass a list of [validation rules](https://github.com/InitPHP/Validation)
as the third argument. When the resolved value fails, the default is
returned:

```php
// if year is present and within 1970–2070 use it, otherwise 2015
$year = $input->getPost('year', 2015, ['range(1970...2070)']);

// required + must equal the password_retype field
$password = $input->post('password', null, ['required', 'again(password_retype)']);
```

> The first source that **owns** the key is the one validated; a present
> but invalid value returns the default and does **not** fall through to
> the next source.

## Presence checks

```php
$input->hasGet('name');  // isset($_GET['name']) — case-sensitive
$input->hasPost('email');
$input->hasRaw('token');
```

## Documentation

Full developer documentation lives in [`docs/`](docs/README.md):
getting started, each source, the priority model, validation, the
facade, a complete API reference and an FAQ.

## Testing

```bash
composer test       # PHPUnit
composer stan       # PHPStan (level max)
composer cs-check   # PHP-CS-Fixer (dry-run)
composer ci         # all of the above
```

## Credits

- [Muhammet ŞAFAK](https://www.muhammetsafak.com.tr) <<info@muhammetsafak.com.tr>>

## License

Released under the [MIT License](./LICENSE).
