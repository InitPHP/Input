# Reading Sources

The package recognises **three** input sources, each with its own accessor
and presence check.

| Method   | Source                          | Presence |
| -------- | ------------------------------- | -------- |
| `get()`  | `$_GET` (query string)          | `hasGet()`  |
| `post()` | `$_POST` (submitted form fields)| `hasPost()` |
| `raw()`  | decoded JSON `php://input` body | `hasRaw()`  |

All three share the same signature:

```php
public function get(string $key, mixed $default = null, ?array $validation = null): mixed;
```

- **`$key`** — the parameter name. Matched **case-sensitively**.
- **`$default`** — returned when the key is absent (or fails validation).
  Defaults to `null`.
- **`$validation`** — an optional list of rules; see [Validation](Validation.md).

## Working example

```php
use InitPHP\Input\Inputs;

$input = new Inputs(
    get:  ['q' => 'shoes', 'page' => '2'],
    post: ['email' => 'jane@example.com'],
    raw:  ['filters' => ['size' => 42], 'inStock' => true],
);

$input->get('q');               // 'shoes'
$input->get('page', 1);         // '2'      — query values are strings
$input->get('sort', 'newest');  // 'newest' — absent, so the default

$input->post('email');          // 'jane@example.com'

$input->raw('inStock');         // true     — JSON keeps its decoded type
$input->raw('filters');         // ['size' => 42]
```

## Defaults and value types

The default is the **second** argument; when omitted it is `null`.

```php
$input = new Inputs(get: ['page' => '2']);

$input->get('page', 1);     // '2'  (string — not the int 2)
$input->get('missing', 1);  // 1    (the default)
$input->get('missing');     // null
```

> Query and form values are always **strings** — that is what PHP's SAPI
> provides. JSON-body values keep the type they decode to (`int`, `bool`,
> `array`, …). The library never casts for you.

## The `raw` (JSON body) source

`raw()` reads the request body once from `php://input` and JSON-decodes it.
Only a JSON **object** or **array** becomes usable data; anything else is
treated as empty:

```php
// Request body: {"name":"Jane","tags":["a","b"]}
$input = new Inputs();          // raw read from php://input
$input->raw('name');            // 'Jane'
$input->raw('tags');            // ['a', 'b']

// Request body: 42  (a bare scalar)
$input = new Inputs();
$input->raw('anything', 'x');   // 'x' — a scalar body decodes to nothing
```

### Already decoded the body?

If you have the decoded body in hand, pass it directly and skip the
`php://input` read entirely:

```php
$body  = json_decode(file_get_contents('php://input'), true);
$input = new Inputs(raw: is_array($body) ? $body : []);
```

### The safe decoder is reusable

The exact guard the constructor uses is exposed as a static helper, so you
can decode a body yourself without risking a `TypeError`:

```php
use InitPHP\Input\Inputs;

Inputs::decodeJsonBody('{"a":1}'); // ['a' => 1]
Inputs::decodeJsonBody('[1,2,3]'); // [1, 2, 3]
Inputs::decodeJsonBody('42');      // []  — scalar
Inputs::decodeJsonBody('"hi"');    // []  — scalar
Inputs::decodeJsonBody('oops');    // []  — invalid JSON
Inputs::decodeJsonBody('');        // []  — empty body
```

## Sources are flat — no dot paths

Each source is stored as a **flat** bag, so a dotted key is a literal key,
not a path into nested data:

```php
$input = new Inputs(raw: ['user' => ['name' => 'Jane']]);

$input->raw('user.name');          // null — there is no literal 'user.name' key
$input->raw('user')['name'] ?? null; // 'Jane' — read the array, then index it
```

## Expected output

| Call | Result |
| --- | --- |
| `get('page', 1)` | `'2'` (string) |
| `get('sort', 'newest')` | `'newest'` |
| `raw('inStock')` | `true` (bool) |
| `decodeJsonBody('42')` | `[]` |

## Common mistakes

- **Reading `php://input` twice.** The body stream can usually be read only
  once per request. Build `Inputs` once (or use the facade) and reuse it.
- **Putting the default in the wrong argument.** The default is the second
  argument; the third is the validation rule list.
- **Expecting type casting.** `get('page', 1)` returns `'2'`, a string.

## Next

- [Source Priority](Source-Priority.md)
- [Validation](Validation.md)
- [Presence Checks](Presence-Checks.md)
