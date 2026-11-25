# Changelog

All notable changes to **initphp/input** will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [3.0.1] - 2026-11-25

### Documentation

- The README and Quick Start state that `Input` returns request data as-is and
  show escaping it (`htmlspecialchars()`) before it is printed into HTML.
- The `getRaw()` recipe uses a non-secret `locale` parameter, and explains that
  credentials belong in the `Authorization` header or the request body, not in the
  query string.

## [3.0.0] - 2026-11-22

### Changed (breaking)

- **Requires `initphp/parameterbag` `^3.0` and `initphp/validation` `^3.0`** (were `^2.0`). Projects that depend on either package directly must raise their constraint too.
- **Requires PHP `^8.2`.** Support for PHP 8.1 is dropped; CI now runs PHP 8.2, 8.3, 8.4 and 8.5.
- **Coding standard moved to PER-CS** through the shared `.php-cs-fixer.dist.php`; the whole code base was reformatted.
