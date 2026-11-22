# Installation

## Requirements

| Requirement | Version |
| --- | --- |
| PHP | 8.2 or later |
| `ext-json` | bundled with PHP |
| [`initphp/parameterbag`](https://github.com/InitPHP/ParameterBag) | `^2.0` |
| [`initphp/validation`](https://github.com/InitPHP/Validation) | `^2.0` |

The two InitPHP dependencies are installed automatically by Composer.

## Install with Composer

```bash
composer require initphp/input
```

Then make sure the Composer autoloader is loaded once in your application's
entry point:

```php
require_once __DIR__ . '/vendor/autoload.php';
```

## Verify the install

```php
<?php
declare(strict_types=1);
require __DIR__ . '/vendor/autoload.php';

use InitPHP\Input\Inputs;

$input = new Inputs(get: ['ping' => 'pong']);

echo $input->get('ping'); // pong
```

If that prints `pong`, you are ready to go.

## The two classes you will use

```php
use InitPHP\Input\Inputs;               // the instance API
use InitPHP\Input\Facade\Inputs as Input; // the static facade
```

- **`Inputs`** — create it yourself (`new Inputs()`), optionally with
  explicit data. Best for dependency injection and tests.
- **`Facade\Inputs`** — a static proxy that lazily builds one `Inputs`
  from the superglobals. Best for quick reads in application code.

Both expose exactly the same methods. See [The Facade](The-Facade.md) for the
difference and when to use which.

## Next

- [Quick Start](Quick-Start.md)
- [Reading Sources](Reading-Sources.md)
