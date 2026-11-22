# Migration from 1.x

Version **2.0 is a breaking release**. It raises the PHP floor, moves to the
2.0 line of its dependencies, and fixes long-standing correctness issues in
the way state was shared. Most application code that used the facade keeps
working unchanged; the breaking changes mostly affect edge cases and direct
instantiation.

> This page describes the 1.x to 2.0 changes, which raised the PHP floor to
> 8.1. The development line after 2.0.0 raises it again to PHP 8.2 (see the
> [CHANGELOG](https://github.com/InitPHP/Input/blob/main/CHANGELOG.md)),
> so check [Installation](Installation.md) for the current requirement.

## At a glance

| Area | 1.x | 2.0 |
| --- | --- | --- |
| Minimum PHP | 7.2 | **8.1** |
| `initphp/parameterbag` | `^1.1` | **`^2.0`** |
| `initphp/validation` | `^1.0` | **`^2.0`** |
| Key matching | case-**insensitive** | case-**sensitive** |
| Instance state | shared `static` bags | per-instance, isolated |
| Destructor | `__destruct` cleared shared bags | removed |
| Facade instance `__call` | present | removed (`__callStatic` only) |
| New: `InputInterface` | — | added |
| New: constructor injection | — | `new Inputs(get:…, post:…, raw:…)` |
| New: `Inputs::decodeJsonBody()` | — | added |
| New: `Facade::setInstance()` / `reset()` | — | added |

## 1. Bump your PHP and dependencies

Version 2.0 requires **PHP 8.1+** because `initphp/validation ^2.0` does.
Update your environment, then:

```bash
composer require initphp/input:^2.0
```

## 2. Keys are now case-sensitive

1.x folded every key to lower-case, so `get('Name')` and `get('name')` were
the same key. **2.0 matches keys exactly**, mirroring how HTTP query and
body parameters actually behave.

```php
$input = new Inputs(get: ['Name' => 'Jane']);

$input->get('Name'); // 'Jane'
$input->get('name'); // null  (1.x would have returned 'Jane')
```

**If you relied on the old behaviour**, normalise the key yourself before
calling:

```php
$input->get(strtolower($key));
```

## 3. Instances no longer share state

In 1.x the three source bags were `static` and a destructor cleared them,
which meant a second `Inputs` instance — or a temporary one being garbage
collected — could silently empty the data for everyone.

**2.0 stores state per instance.** Each `Inputs` is independent, and the
destructor is gone. This is the bug fix you most likely want, but if any of
your code *depended* on two `Inputs` objects sharing data through statics,
that no longer happens — give them the same data explicitly, or use the
[facade](The-Facade.md), which holds a single shared instance.

## 4. The facade gained inject/reset, lost instance `__call`

The facade is still used exactly the same way:

```php
use InitPHP\Input\Facade\Inputs as Input;

Input::get('name', 'John'); // unchanged
```

What changed:

- The unused instance-level `__call` was removed; only the static
  `__callStatic` proxy remains. (Calling facade methods on an *instance* of
  the facade was never the intended usage.)
- New `Input::setInstance(...)` and `Input::reset()` let you inject a
  configured instance — see [The Facade](The-Facade.md) and [Testing](Testing.md).

## 5. Validation rule names

Validation moved to 2.0 as well. The rules used by this package's examples —
`required`, `range(...)`, `again(...)`, `integer`, `mail`, … — still exist
and behave the same. If you used more exotic rules, check the Validation
wiki's
[Rules Reference](https://github.com/InitPHP/Validation/wiki/Rules-Reference)
and [Migration from 1.x](https://github.com/InitPHP/Validation/wiki/Migration-from-1.x).

## 6. New things you may want to adopt

- **`InputInterface`** — type-hint it in your services instead of the
  concrete class (see [Recipes](Recipes.md#dependency-injection-with-the-interface)).
- **Constructor injection** — `new Inputs(get: [...], post: [...], raw: [...])`
  for tests and custom request abstractions.
- **`Inputs::decodeJsonBody()`** — a safe, reusable JSON-body decoder.

## Upgrade checklist

- [ ] PHP runtime is 8.1+
- [ ] `composer require initphp/input:^2.0`
- [ ] Audit any case-sensitive key lookups
- [ ] Remove any code that relied on static cross-instance state
- [ ] (Optional) switch service type-hints to `InputInterface`

## Next

- [Quick Start](Quick-Start.md)
- [API Reference](API-Reference.md)
- [FAQ](FAQ.md)
