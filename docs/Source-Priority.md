# Source Priority

Sometimes a value could arrive in more than one place — a query string
*or* a form field *or* a JSON body. The priority helpers read several
sources in a defined order and hand you the first match.

## The rule

> A priority helper walks its sources in the order its name reads, left to
> right, and returns the value of **the first source that contains the
> key**. Once a source owns the key the lookup stops — remaining sources
> are never consulted, even if validation later rejects that value.

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

When the earlier sources do not have the key, the helper falls through to
the next one, and finally to the default:

```php
$input = new Inputs(post: ['year' => '2003']); // no "year" in the query

$input->getPost('year', 2015); // '2003' — fell through to post
$input->getPost('age', 2015);  // 2015   — no source has it, so the default
```

## The twelve helpers

| Method         | Order              |
| -------------- | ------------------ |
| `getPost`      | get → post         |
| `getRaw`       | get → raw          |
| `getPostRaw`   | get → post → raw   |
| `getRawPost`   | get → raw → post   |
| `postGet`      | post → get         |
| `postRaw`      | post → raw         |
| `postGetRaw`   | post → get → raw   |
| `postRawGet`   | post → raw → get   |
| `rawGet`       | raw → get          |
| `rawPost`      | raw → post         |
| `rawGetPost`   | raw → get → post   |
| `rawPostGet`   | raw → post → get   |

Read the method name literally: `postRawGet` is **post, then raw, then
get**. Every helper shares the single-source signature:

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

Remove a source and the order keeps working:

```php
$input = new Inputs(raw: ['token' => 'from-body']); // only the body has it

$input->getPostRaw('token', 'none'); // 'from-body' — skipped get and post
$input->postGet('token', 'none');    // 'none'      — neither post nor get has it
```

## Presence decides priority — not validity

This is the one subtlety worth internalising. Priority is decided by
**presence**, not by whether the value is valid. If the first source has
the key but the value fails validation, you get the **default** — the
helper does **not** try the next source.

```php
$input = new Inputs(
    get:  ['year' => '3000'], // present but out of range
    post: ['year' => '1999'], // valid, but never reached
);

$input->getPost('year', 2015, ['range(1970...2070)']); // 2015
```

If you actually want the valid POST value here, either read the sources
separately and decide yourself, or order the helper so the source you
trust comes first. See [Validation](Validation.md) for the full discussion.

## Common mistakes

- **Expecting "first valid value" semantics.** It is "first *present*
  value". A present-but-invalid value returns the default, full stop.
- **Confusing the order.** The name is the order: `rawPostGet` ≠
  `rawGetPost`.

## Next

- [Validation](Validation.md)
- [API Reference](API-Reference.md)
