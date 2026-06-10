# Source priority

**Goal:** read a value that may live in more than one source, choosing
the source by a defined order.

## The rule

A priority helper walks its sources in the order its name reads, left to
right, and returns the value of **the first source that contains the
key**. Remaining sources are never consulted once a source owns the key —
even if validation later rejects that value (see
[Validation](validation.md)).

```php
use InitPHP\Input\Inputs;

// "year" is in both the query string and the form body.
$input = new Inputs(
    get:  ['year' => '1999'],
    post: ['year' => '2003'],
);

$input->getPost('year'); // '1999' — get is checked first and has it
$input->postGet('year'); // '2003' — post is checked first and has it
```

When the earlier sources do not have the key, the helper falls through:

```php
$input = new Inputs(post: ['year' => '2003']); // no "year" in the query

$input->getPost('year', 2015); // '2003' — fell through to post
$input->getPost('age', 2015);  // 2015   — no source has it, so the default
```

## The twelve helpers

| Method         | Order                          |
|----------------|--------------------------------|
| `getPost`      | get → post                     |
| `getRaw`       | get → raw                      |
| `getPostRaw`   | get → post → raw               |
| `getRawPost`   | get → raw → post               |
| `postGet`      | post → get                     |
| `postRaw`      | post → raw                     |
| `postGetRaw`   | post → get → raw               |
| `postRawGet`   | post → raw → get               |
| `rawGet`       | raw → get                      |
| `rawPost`      | raw → post                     |
| `rawGetPost`   | raw → get → post               |
| `rawPostGet`   | raw → post → get               |

All share the single-source signature:

```php
public function getPostRaw(string $key, mixed $default = null, ?array $validation = null): mixed;
```

## Worked example

```php
$input = new Inputs(
    get:  ['token' => 'from-query'],
    post: ['token' => 'from-form'],
    raw:  ['token' => 'from-body'],
);

$input->getPostRaw('token'); // 'from-query'
$input->postRawGet('token'); // 'from-form'
$input->rawGetPost('token'); // 'from-body'
```

## Common mistakes

- **Expecting "first valid value" semantics.** Priority is decided by
  *presence*, not validity. If the first source has the key but the value
  fails validation, you get the default — the next source is not tried.
  See [Validation](validation.md).
- **Confusing the order.** Read the method name literally:
  `postRawGet` is post, then raw, then get.

## Next

- [Validation](validation.md)
- [API reference](../api-reference.md)
