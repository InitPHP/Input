# Reading sources

**Goal:** read a value from one specific source and understand defaults
and value types.

The three single-source accessors are:

| Method   | Source                          |
|----------|---------------------------------|
| `get()`  | `$_GET` (query string)          |
| `post()` | `$_POST` (submitted form fields)|
| `raw()`  | decoded JSON `php://input` body |

Each has the same signature:

```php
public function get(string $key, mixed $default = null, ?array $validation = null): mixed;
```

## Working example

```php
use InitPHP\Input\Inputs;

$input = new Inputs(
    get: ['q' => 'shoes', 'page' => '2'],
    post: ['email' => 'jane@example.com'],
    raw: ['filters' => ['size' => 42], 'inStock' => true],
);

$input->get('q');               // 'shoes'
$input->get('page', 1);         // '2'   — query values are strings
$input->get('sort', 'newest');  // 'newest' — absent, so the default

$input->post('email');          // 'jane@example.com'

$input->raw('inStock');         // true  — JSON keeps its decoded type
$input->raw('filters');         // ['size' => 42]
```

## The raw (JSON body) source

`raw()` reads the request body once from `php://input` and JSON-decodes
it. Only a JSON **object** or **array** becomes usable data; anything
else is treated as empty:

```php
// Request body: {"name":"Jane","tags":["a","b"]}
$input = new Inputs();          // raw read from php://input
$input->raw('name');            // 'Jane'
$input->raw('tags');            // ['a', 'b']

// Request body: 42   (a bare scalar)
$input = new Inputs();
$input->raw('anything', 'x');   // 'x' — a scalar body decodes to nothing
```

If you already have the decoded body, pass it in directly and skip the
`php://input` read:

```php
$body  = json_decode(file_get_contents('php://input'), true);
$input = new Inputs(raw: is_array($body) ? $body : []);
```

The same guard the constructor uses is exposed as a static helper:

```php
Inputs::decodeJsonBody('{"a":1}'); // ['a' => 1]
Inputs::decodeJsonBody('42');      // []  — scalar
Inputs::decodeJsonBody('oops');    // []  — invalid JSON
Inputs::decodeJsonBody('');        // []  — empty body
```

## Expected output

| Call                              | Result               |
|-----------------------------------|----------------------|
| `get('page', 1)`                  | `'2'` (string)       |
| `get('sort', 'newest')`           | `'newest'`           |
| `raw('inStock')`                  | `true` (bool)        |
| `decodeJsonBody('42')`            | `[]`                 |

## Common mistakes

- **Reading `php://input` twice.** The body stream can usually be read
  only once per request. Construct `Inputs` once (or use the facade) and
  reuse it.
- **Forgetting the default is the *second* argument.** The third
  argument is the validation rule list, not the default.

## Next

- [Source priority](source-priority.md)
- [Validation](validation.md)
