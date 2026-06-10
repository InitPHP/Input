# The facade

**Goal:** call the input API statically, and control the instance behind
it for tests or dependency injection.

`InitPHP\Input\Facade\Inputs` is a thin static proxy over a single
`InputInterface` instance. On the first call it lazily builds an
`Inputs` from the superglobals and the request body, then reuses it.

```php
use InitPHP\Input\Facade\Inputs as Input;

Input::get('name', 'John');
Input::getPost('year', 2015, ['range(1970...2070)']);
Input::hasPost('email');
```

Every method documented for `Inputs` is callable statically through the
facade.

## Injecting a backing instance

`setInstance()` replaces the instance the facade delegates to. This is
the seam for tests and for feeding request data that does not come from
the superglobals.

```php
use InitPHP\Input\Facade\Inputs as Input;
use InitPHP\Input\Inputs;

Input::setInstance(new Inputs(
    get:  ['name' => 'Jane'],
    post: ['email' => 'jane@example.com'],
));

Input::get('name');   // 'Jane'
Input::hasPost('email'); // true
```

## Resetting

`reset()` forgets the backing instance; the next call rebuilds it from
the current superglobals / body. Call it in your test teardown so one
test cannot leak into the next:

```php
protected function tearDown(): void
{
    \InitPHP\Input\Facade\Inputs::reset();
}
```

## Facade vs. instance

| | Facade | Instance |
|---|--------|----------|
| Setup | none | `new Inputs(...)` |
| Access | `Input::get(...)` | `$input->get(...)` |
| Best for | app code, quick reads | DI, services, isolated tests |
| Swap data | `setInstance()` / `reset()` | constructor arguments |

Both share the same behaviour; the facade simply holds one instance for
you.

## Common mistakes

- **Leaking state between tests.** The facade caches its instance for the
  whole process. Always `reset()` (or `setInstance()`) between tests.
- **Mixing facade and ad-hoc instances expecting shared data.** They are
  independent objects. The facade reads its own instance; a separate
  `new Inputs()` reads its own sources.

## Next

- [API reference](../api-reference.md)
- [Getting started](../getting-started.md)
