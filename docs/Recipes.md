# Recipes

Copy-paste patterns for common tasks. Each one uses only the public API.

## Pagination parameters

Read `page` and `perPage` from the query string, validated and clamped to
sane bounds.

```php
use InitPHP\Input\Facade\Inputs as Input;

$page = (int) Input::get('page', 1, ['integer', 'range(1...)']);
$per  = (int) Input::get('perPage', 20, ['integer', 'range(1...100)']);

// GET /?page=3&perPage=50  → $page = 3, $per = 50
// GET /?page=abc           → $page = 1  (invalid → default)
$offset = ($page - 1) * $per;
```

> `range(1...)` is an open upper bound — "at least 1". See the
> [Rules Reference](https://github.com/InitPHP/Validation/wiki/Rules-Reference).

## Search filters from the query string

```php
use InitPHP\Input\Facade\Inputs as Input;

$filters = [
    'q'     => Input::get('q', ''),
    'sort'  => Input::get('sort', 'newest', ['only(newest,price,rating)']),
    'inStock' => Input::hasGet('inStock'),
];
```

## A JSON API endpoint

Read the request body once and pull validated fields from it.

```php
use InitPHP\Input\Inputs;

// Body: {"email":"ada@example.com","age":34}
$input = new Inputs(); // raw read from php://input

$email = $input->raw('email', null, ['required', 'mail']);
$age   = $input->raw('age', null, ['integer', 'range(18...120)']);

if ($email === null || $age === null) {
    http_response_code(422);
    return;
}
```

## Accept a value from the query *or* the JSON body

Useful for endpoints that take parameters either in the URL or the body.

```php
use InitPHP\Input\Facade\Inputs as Input;

// Checks $_GET first, then the JSON body.
$apiKey = Input::getRaw('api_key', null, ['required']);
```

## A login form

```php
use InitPHP\Input\Inputs;

$input = new Inputs(); // reads $_POST

$email    = $input->post('email', null, ['required', 'mail']);
$password = $input->post('password', null, ['required', 'length(8...)']);

if ($email === null || $password === null) {
    // show the form again with an error
}
```

## A registration form with password confirmation

```php
use InitPHP\Input\Inputs;

$input = new Inputs(); // reads $_POST

$password = $input->post('password', null, [
    'required',
    'length(8...)',
    'again(password_confirm)', // must equal the password_confirm field
]);

// $password is null when missing, too short, or the confirmation differs.
```

## Dependency injection with the interface

Type-hint `InputInterface` so your services do not depend on the concrete
class or the facade — and are trivial to test.

```php
use InitPHP\Input\InputInterface;

final class SearchController
{
    public function __construct(private InputInterface $input) {}

    public function index(): array
    {
        return [
            'query' => $this->input->get('q', ''),
            'page'  => (int) $this->input->get('page', 1, ['integer', 'range(1...)']),
        ];
    }
}

// Wiring (in your container / front controller):
$controller = new SearchController(new \InitPHP\Input\Inputs());
```

In a test you pass an `Inputs` seeded with explicit data — no globals, no
HTTP:

```php
$controller = new SearchController(new \InitPHP\Input\Inputs(
    get: ['q' => 'shoes', 'page' => '2'],
));
```

## Wrapping an existing request array

Already have the request data as arrays (from a framework, a PSR-7 request,
or a test fixture)? Hand them in directly:

```php
use InitPHP\Input\Inputs;

$input = new Inputs(
    get:  $request->getQueryParams(),
    post: (array) $request->getParsedBody(),
    raw:  json_decode((string) $request->getBody(), true) ?: [],
);
```

## Next

- [Validation](Validation.md)
- [Testing](Testing.md)
- [API Reference](API-Reference.md)
