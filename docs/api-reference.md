# API reference

Every public symbol in the package, grouped by type. Unless noted, the
shared accessor signature is:

```php
function (string $key, mixed $default = null, ?array $validation = null): mixed
```

- `$key` — the parameter name (case-sensitive).
- `$default` — returned when the key is absent or the value fails
  validation. Defaults to `null`.
- `$validation` — a list of [validation rules](https://github.com/InitPHP/Validation)
  (DSL strings and/or callables). Empty or `null` skips validation.

---

## Interface `InitPHP\Input\InputInterface`

### Single source

| Method   | Reads from                       |
|----------|----------------------------------|
| `get()`  | `$_GET`                          |
| `post()` | `$_POST`                         |
| `raw()`  | decoded JSON `php://input` body  |

### Priority (first source that has the key wins)

| Method         | Order                |
|----------------|----------------------|
| `getPost()`    | get → post           |
| `getRaw()`     | get → raw            |
| `getPostRaw()` | get → post → raw     |
| `getRawPost()` | get → raw → post     |
| `postGet()`    | post → get           |
| `postRaw()`    | post → raw           |
| `postGetRaw()` | post → get → raw     |
| `postRawGet()` | post → raw → get     |
| `rawGet()`     | raw → get            |
| `rawPost()`    | raw → post           |
| `rawGetPost()` | raw → get → post     |
| `rawPostGet()` | raw → post → get     |

### Presence

| Method                       | Returns                              |
|------------------------------|--------------------------------------|
| `hasGet(string $key): bool`  | whether `$key` exists in `$_GET`     |
| `hasPost(string $key): bool` | whether `$key` exists in `$_POST`    |
| `hasRaw(string $key): bool`  | whether `$key` exists in the body    |

A key whose stored value is `null` still counts as present.

---

## Class `InitPHP\Input\Inputs`

`final`, implements `InputInterface`.

### `__construct()`

```php
public function __construct(
    ?array $get = null,
    ?array $post = null,
    ?array $raw = null,
    ?\InitPHP\Validation\Validation $validation = null
)
```

Each `null` source falls back to its global: `$get` → `$_GET`, `$post` →
`$_POST`, `$raw` → the decoded `php://input` body. `$validation` lets you
supply a preconfigured validator (e.g. with custom rules or a locale);
omitted, a fresh one is created.

### `decodeJsonBody()`

```php
public static function decodeJsonBody(string $body): array
```

JSON-decode a raw request body into an input array. An empty string, a
scalar (`5`, `"x"`, `true`), or malformed JSON returns `[]`, so a scalar
payload can never reach the underlying container.

---

## Facade `InitPHP\Input\Facade\Inputs`

`final`. A static proxy over a single `InputInterface` instance; all
`InputInterface` methods are callable statically (e.g. `Inputs::get(...)`).

| Method                                       | Description                                             |
|----------------------------------------------|---------------------------------------------------------|
| `setInstance(InputInterface $instance): void`| Replace the backing instance (tests, DI).               |
| `reset(): void`                              | Forget the backing instance; the next call rebuilds it. |

---

## Behaviour notes

- **Case sensitivity:** keys are matched exactly; `name` and `Name`
  differ.
- **Value types:** values are returned as the source provides them —
  query/form values are strings, JSON-body values keep their decoded
  type.
- **No fall-through on invalid:** in a priority helper, the first source
  that has the key is committed to; an invalid value returns the default
  rather than trying the next source.
- **Isolation:** instances share no static state; two `Inputs` objects
  never interfere.
