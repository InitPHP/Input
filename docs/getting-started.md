# Getting started

**Goal:** install the package, create an `Inputs` instance (or use the
facade), and read your first request value.

## Install

```bash
composer require initphp/input
```

Requirements: PHP 8.1+, `ext-json`, and the `initphp/parameterbag ^2.0`
and `initphp/validation ^2.0` packages (pulled in automatically).

## Two ways to use it

### 1. The facade (zero setup)

The facade builds a single backing instance from the superglobals on
first use and reuses it for the rest of the request.

```php
require_once 'vendor/autoload.php';

use InitPHP\Input\Facade\Inputs as Input;

// GET /?name=Jane
echo Input::get('name', 'John');
```

**Expected output:** `Jane` (or `John` when `name` is absent).

### 2. An instance

```php
use InitPHP\Input\Inputs;

$input = new Inputs(); // reads $_GET, $_POST and php://input

echo $input->get('name', 'John');
```

`new Inputs()` with no arguments reads the live superglobals and request
body. To make the object self-contained — for tests, CLI tooling, or a
custom request abstraction — pass the sources in:

```php
$input = new Inputs(
    get: ['name' => 'Jane'],
    post: ['email' => 'jane@example.com'],
    raw: ['token' => 'abc123'],
);

echo $input->get('name');  // 'Jane'
echo $input->post('email'); // 'jane@example.com'
echo $input->raw('token');  // 'abc123'
```

Any argument left as `null` falls back to its global: `get` to `$_GET`,
`post` to `$_POST`, and `raw` to the decoded `php://input` body.

## What you get back

Every accessor returns either the stored value or the **default** you
pass as the second argument (`null` when omitted):

```php
$input = new Inputs(get: ['page' => '2']);

$input->get('page', 1);     // '2'   (note: still a string from the query)
$input->get('missing', 1);  // 1     (the default)
$input->get('missing');     // null
```

> Values are returned exactly as the source provides them. Query and form
> values are strings; JSON-body values keep their decoded type
> (`int`, `bool`, `array`, …).

## Common mistakes

- **Expecting type casting.** `get('page', 1)` returns the *string*
  `'2'`, not an integer. Cast it yourself, or validate with a rule such
  as `['integer']`.
- **Assuming case-insensitive keys.** `get('Name')` and `get('name')`
  are different keys. See the [FAQ](faq.md).

## Next

- [Reading sources](usage/reading-sources.md)
- [Source priority](usage/source-priority.md)
