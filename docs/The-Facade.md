# The Facade

`InitPHP\Input\Facade\Inputs` is a thin **static proxy** over a single
`InputInterface` instance. On the first call it lazily builds an `Inputs`
from the superglobals and the request body, then reuses it for the rest of
the request.

```php
use InitPHP\Input\Facade\Inputs as Input;

Input::get('name', 'John');
Input::getPost('year', 2015, ['range(1970...2070)']);
Input::hasPost('email');
```

Every method documented for [`Inputs`](API-Reference.md#class-inputs) is
callable statically through the facade — same names, same arguments, same
return values.

## How it works

The facade forwards static calls to its backing instance via
`__callStatic`. The instance is created once and cached:

```php
// First call: builds new Inputs() from $_GET, $_POST and php://input.
Input::get('name');

// Later calls: reuse the same instance.
Input::post('email');
```

## Injecting a backing instance

`setInstance()` replaces the instance the facade delegates to. This is the
seam for tests and for feeding request data that does not come from the
superglobals.

```php
use InitPHP\Input\Facade\Inputs as Input;
use InitPHP\Input\Inputs;

Input::setInstance(new Inputs(
    get:  ['name' => 'Jane'],
    post: ['email' => 'jane@example.com'],
));

Input::get('name');      // 'Jane'
Input::hasPost('email'); // true
```

## Resetting

`reset()` forgets the backing instance; the next call rebuilds it from the
current superglobals / body. Call it in your test teardown so one test
cannot leak into the next:

```php
protected function tearDown(): void
{
    \InitPHP\Input\Facade\Inputs::reset();
}
```

See [Testing](Testing.md) for the full pattern.

## Facade vs. instance

| | Facade | Instance |
| --- | --- | --- |
| Setup | none | `new Inputs(...)` |
| Access | `Input::get(...)` | `$input->get(...)` |
| Best for | application code, quick reads | DI, services, isolated tests |
| Swap data | `setInstance()` / `reset()` | constructor arguments |

Both share the same behaviour; the facade simply holds one instance for
you. For dependency injection, prefer type-hinting
[`InputInterface`](API-Reference.md#interface-inputinterface) and injecting an
`Inputs` instance rather than reaching for the facade.

## Common mistakes

- **Leaking state between tests.** The facade caches its instance for the
  whole process. Always `reset()` (or `setInstance()`) between tests.
- **Expecting the facade and an ad-hoc `new Inputs()` to share data.** They
  are independent objects, each with its own sources.

## Next

- [API Reference](API-Reference.md)
- [Testing](Testing.md)
