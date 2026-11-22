# Quick Start

From `composer require` to reading validated request input in a few minutes.

## 1. Pick a style

### The facade — zero setup

The facade builds one `Inputs` from the superglobals on first use and reuses
it for the rest of the request.

```php
<?php
declare(strict_types=1);
require __DIR__ . '/vendor/autoload.php';

use InitPHP\Input\Facade\Inputs as Input;

// GET /?name=Jane
echo Input::get('name', 'John'); // Jane
```

### An instance — explicit and testable

```php
<?php
declare(strict_types=1);
require __DIR__ . '/vendor/autoload.php';

use InitPHP\Input\Inputs;

$input = new Inputs(); // reads $_GET, $_POST and php://input

echo $input->get('name', 'John');
```

Pass the sources in to make the object self-contained (great for tests):

```php
$input = new Inputs(
    get:  ['name' => 'Jane'],
    post: ['email' => 'jane@example.com'],
    raw:  ['token' => 'abc123'],
);
```

## 2. Read a value from one source

```php
$input->get('name', 'guest');   // from $_GET
$input->post('email');          // from $_POST
$input->raw('token');           // from the JSON request body
```

The second argument is the **default**, returned when the key is absent
(`null` when you omit it).

## 3. Read with a priority order

When a value may live in more than one place, the priority helpers return
the **first source that has the key**:

```php
// Look in the query string first, then the form body.
$year = $input->getPost('year', 2015);
```

The full set: `getPost`, `getRaw`, `getPostRaw`, `getRawPost`, `postGet`,
`postRaw`, `postGetRaw`, `postRawGet`, `rawGet`, `rawPost`, `rawGetPost`,
`rawPostGet`. See [Source Priority](Source-Priority.md).

## 4. Validate as you read

Pass a list of [validation rules](https://github.com/InitPHP/Validation) as
the third argument. A value that fails its rules yields the default:

```php
// Accept a year between 1970 and 2070, else fall back to 2015.
$year = $input->getPost('year', 2015, ['range(1970...2070)']);

// Require a password that matches the confirmation field.
$pwd = $input->post('password', null, ['required', 'again(password_retype)']);
```

See [Validation](Validation.md).

## 5. Check whether a key was sent

```php
if ($input->hasPost('newsletter')) {
    // the checkbox was submitted (even if its value is empty)
}
```

See [Presence Checks](Presence-Checks.md).

## A complete example

```php
<?php
declare(strict_types=1);
require __DIR__ . '/vendor/autoload.php';

use InitPHP\Input\Inputs;

$input = new Inputs(
    get:  ['q' => 'shoes', 'page' => '2'],
    post: ['sort' => 'price'],
);

$query = $input->get('q', '');                          // 'shoes'
$page  = (int) $input->get('page', 1, ['integer']);     // 2
$sort  = $input->getPost('sort', 'newest', ['only(price,newest,rating)']);

// $query = 'shoes', $page = 2, $sort = 'price'
```

> Values come back exactly as the source provides them — query and form
> values are **strings**. Cast them yourself, or validate with a rule like
> `['integer']` and convert afterwards.

## Next

- [Reading Sources](Reading-Sources.md) — the three accessors in depth.
- [Source Priority](Source-Priority.md) — the priority model.
- [Validation](Validation.md) — rules and the no-fall-through behaviour.
