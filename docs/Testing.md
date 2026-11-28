# Testing

The package is designed to be tested without touching `$_GET`, `$_POST` or
`php://input`. You inject the data instead.

## Testing code that takes an `Inputs` instance

Construct `Inputs` with explicit sources and assert on the result. No
globals, no HTTP request.

```php
use InitPHP\Input\Inputs;
use PHPUnit\Framework\TestCase;

final class ExampleTest extends TestCase
{
    public function testReadsTheQueryValue(): void
    {
        $input = new Inputs(get: ['name' => 'Jane']);

        self::assertSame('Jane', $input->get('name'));
    }

    public function testInvalidValueFallsBackToDefault(): void
    {
        $input = new Inputs(get: ['year' => '3000']);

        self::assertSame(2015, $input->get('year', 2015, ['range(1970...2070)']));
    }
}
```

## Testing the JSON body decoder

`decodeJsonBody()` is a pure static method — ideal for table-driven tests.

```php
use InitPHP\Input\Inputs;

self::assertSame(['a' => 1], Inputs::decodeJsonBody('{"a":1}'));
self::assertSame([], Inputs::decodeJsonBody('42'));   // scalar
self::assertSame([], Inputs::decodeJsonBody('oops')); // invalid
self::assertSame([], Inputs::decodeJsonBody(''));     // empty
```

## Testing code that uses the facade

When the code under test calls the facade statically, inject a seeded
instance with `setInstance()` and clear it in `tearDown()` so tests stay
isolated.

```php
use InitPHP\Input\Facade\Inputs as Input;
use InitPHP\Input\Inputs;
use PHPUnit\Framework\TestCase;

final class FacadeConsumerTest extends TestCase
{
    protected function tearDown(): void
    {
        Input::reset();
    }

    public function testItReadsFromTheFacade(): void
    {
        Input::setInstance(new Inputs(get: ['name' => 'Jane']));

        // ... exercise the code that calls Input::get('name') ...

        self::assertSame('Jane', Input::get('name'));
    }
}
```

> Always `reset()` (or `setInstance()`) between tests. The facade caches its
> backing instance for the whole process, so a leaked instance would bleed
> into later tests.

## Driving the superglobal path

If you really want to exercise the `new Inputs()`-reads-globals branch, set
the superglobals and restore them afterwards:

```php
public function testReadsFromGlobals(): void
{
    $original = $_GET;
    $_GET = ['name' => 'Jane'];

    try {
        self::assertSame('Jane', (new Inputs())->get('name'));
    } finally {
        $_GET = $original;
    }
}
```

Prefer injecting data over this whenever you can — it is cleaner and has no
global side effects.

## Running the package's own test suite

If you are contributing to the package itself:

```bash
composer test       # PHPUnit
composer stan       # PHPStan (level max)
composer cs-check   # PHP-CS-Fixer (dry-run)
composer ci         # all three
```

The suite ships at 100% line coverage and is green on PHP 8.2–8.6.

## Next

- [The Facade](The-Facade.md)
- [Recipes](Recipes.md)
