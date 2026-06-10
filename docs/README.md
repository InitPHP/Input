# Documentation

Developer documentation for the `initphp/input` package. The project
[README](../README.md) gives a one-page overview; this directory goes
deeper.

## Index

- [Getting started](getting-started.md) — install, instantiate, and read
  your first value.
- **Usage**
  - [Reading sources](usage/reading-sources.md) — `get`, `post`, `raw`
    and defaults.
  - [Source priority](usage/source-priority.md) — the twelve priority
    helpers and the "first source that has the key wins" rule.
  - [Validation](usage/validation.md) — per-call rules and the
    no-fall-through behaviour.
  - [Presence checks](usage/presence-checks.md) — `hasGet`, `hasPost`,
    `hasRaw`.
  - [The facade](usage/facade.md) — the static proxy, `setInstance()`
    and `reset()`.
- [API reference](api-reference.md) — every public method, listed.
- [FAQ](faq.md) — common pitfalls and clarifications.

## How to read these docs

Every page is structured as **Goal → Working example → Expected output →
Common mistakes**. Snippets are copy-paste ready against the released
package; outputs were verified against the test suite.

## The three sources at a glance

| Source | Backed by      | Accessor | Presence check |
|--------|----------------|----------|----------------|
| `get`  | `$_GET`        | `get()`  | `hasGet()`     |
| `post` | `$_POST`       | `post()` | `hasPost()`    |
| `raw`  | `php://input`* | `raw()`  | `hasRaw()`     |

\* The raw body is read once and JSON-decoded. A non-object/array or
malformed payload is treated as empty.
