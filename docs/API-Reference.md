# API Reference

Every public symbol in the package. Unless noted, the shared accessor
signature is:

```php
function (string $key, mixed $default = null, ?array $validation = null): mixed
```

- **`$key`** — the parameter name (case-sensitive).
- **`$default`** — returned when the key is absent or the value fails
  validation. Defaults to `null`.
- **`$validation`** — a list of
  [validation rules](https://github.com/InitPHP/Validation/wiki/Rules-Reference)
  (DSL strings and/or callables). Empty or `null` skips validation.

---

## Interface `InputInterface`

`InitPHP\Input\InputInterface` — the contract implemented by `Inputs`.
Type-hint this in your services for dependency injection.

### Single source

| Method   | Reads from                      |
| -------- | ------------------------------- |
| `get()`  | `$_GET`                         |
| `post()` | `$_POST`                        |
| `raw()`  | decoded JSON `php://input` body |

### Priority (first source that has the key wins)

| Method         | Order              |
| -------------- | ------------------ |
| `getPost()`    | get → post         |
| `getRaw()`     | get → raw          |
| `getPostRaw()` | get → post → raw   |
| `getRawPost()` | get → raw → post   |
| `postGet()`    | post → get         |
| `postRaw()`    | post → raw         |
| `postGetRaw()` | post → get → raw   |
| `postRawGet()` | post → raw → get   |
| `rawGet()`     | raw → get          |
| `rawPost()`    | raw → post         |
| `rawGetPost()` | raw → get → post   |
| `rawPostGet()` | raw → post → get   |

### Presence

| Method                       | Returns                            |
| ---------------------------- | ---------------------------------- |
| `hasGet(string $key): bool`  | whether `$key` exists in `$_GET`   |
| `hasPost(string $key): bool` | whether `$key` exists in `$_POST`  |
| `hasRaw(string $key): bool`  | whether `$key` exists in the body  |

A key whose stored value is `null` still counts as present.

---

## Class `Inputs`

`InitPHP\Input\Inputs` — `final`, implements `InputInterface`.

### `__construct()`

```php
public function __construct(
    ?array $get = null,
    ?array $post = null,
    ?array $raw = null,
    ?\InitPHP\Validation\Validation $validation = null
)
```

Builds an instance over the three sources. Each `null` argument falls back
to its global:

| Argument | Falls back to |
| --- | --- |
| `$get`  | `$_GET` |
| `$post` | `$_POST` |
| `$raw`  | the decoded `php://input` body |
| `$validation` | a fresh `Validation` instance |

The argument names are part of the public API — named arguments such as
`new Inputs(get: [...], raw: [...])` are supported and encouraged.

```php
$input = new Inputs(
    get:  ['name' => 'Jane'],
    post: ['email' => 'jane@example.com'],
    raw:  ['token' => 'abc123'],
);
```

### `decodeJsonBody()`

```php
public static function decodeJsonBody(string $body): array
```

JSON-decode a raw request body into an input array. An empty string, a
scalar (`5`, `"x"`, `true`), or malformed JSON returns `[]`, so a scalar
payload can never reach the underlying container.

```php
Inputs::decodeJsonBody('{"a":1}'); // ['a' => 1]
Inputs::decodeJsonBody('42');      // []
Inputs::decodeJsonBody('oops');    // []
Inputs::decodeJsonBody('');        // []
```

---

## Facade `Facade\Inputs`

`InitPHP\Input\Facade\Inputs` — `final`. A static proxy over a single
`InputInterface` instance; all `InputInterface` methods are callable
statically (e.g. `Inputs::get(...)`).

| Method | Description |
| --- | --- |
| `setInstance(InputInterface $instance): void` | Replace the backing instance (tests, DI). |
| `reset(): void` | Forget the backing instance; the next call rebuilds it. |

See [The Facade](The-Facade.md).

---

## Behaviour notes

- **Case sensitivity.** Keys are matched exactly; `name` and `Name` differ.
- **Value types.** Values are returned as the source provides them —
  query/form values are strings, JSON-body values keep their decoded type.
- **Flat storage.** Each source is a flat bag; a dotted key is a literal
  key, not a path into nested data.
- **No fall-through on invalid.** In a priority helper, the first source
  that has the key is committed to; an invalid value returns the default
  rather than trying the next source.
- **Isolation.** Instances share no static state; two `Inputs` objects
  never interfere.

## Next

- [Reading Sources](Reading-Sources.md)
- [Source Priority](Source-Priority.md)
- [Validation](Validation.md)
