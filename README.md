# InitPHP Input

It is a simple library developed to retrieve user inputs by prioritizing or verifying.

[![Latest Stable Version](http://poser.pugx.org/initphp/input/v)](https://packagist.org/packages/initphp/input) [![Total Downloads](http://poser.pugx.org/initphp/input/downloads)](https://packagist.org/packages/initphp/input) [![Latest Unstable Version](http://poser.pugx.org/initphp/input/v/unstable)](https://packagist.org/packages/initphp/input) [![License](http://poser.pugx.org/initphp/input/license)](https://packagist.org/packages/initphp/input) [![PHP Version Require](http://poser.pugx.org/initphp/input/require/php)](https://packagist.org/packages/initphp/input)


## Requirements

- PHP 7.4 or higher
- [InitPHP ParameterBag](https://github.com/InitPHP/ParameterBag)
- [InitPHP Validation](https://github.com/InitPHP/Validation)

## Installation

```
composer require initphp/input
```

## Usage

```php
require_once "vendor/autoload.php";
use \InitPHP\Input\Input;

// echo isset($_GET['name']) ? $_GET['name'] : 'John';
echo Input::get('name', 'John');
```

```php
require_once "vendor/autoload.php";
use \InitPHP\Input\Input;

/**
 * if(isset($_GET['year']) && $_GET['year'] >= 1970 && $_GET['year'] <= 2070){
 *      $year = $_GET['year'];
 * }elseif(isset($_POST['year']) && $_POST['year'] >= 1970 && $_POST['year'] <= 2070){
 *      $year = $_POST['year'];
 * }else{
 *      $year = 2015;
 * }
 */
$year = Input::getPost('year', 2015, ['range(1970...2070)']);
```

## Methods

#### `Input::get()`

```php
public function get(string $key, mixed $default = null, ?array $validation = null): mixed;
```

#### `Input::post()`

```php
public function post(string $key, mixed $default = null, ?array $validation = null): mixed;
```

#### `Input::raw()`

Data from reading `php://input`.

```php
public function raw(string $key, mixed $default = null, ?array $validation = null): mixed;
```

#### `Input::files()`

**Note :** Returns the `$_FILES` data, normalizing if necessary.

```php
public function files(string $key, mixed $default = null): array|mixed;
```

### Getting Input with Priority

#### `Input::getPost()`

`$_GET` -> `$_POST`

```php
public function getPost(string $key, mixed $default = null, ?array $validation = null): mixed;
```

#### `Input::getRaw()`

`$_GET` -> `php://input`

```php
public function getRaw(string $key, mixed $default = null, ?array $validation = null): mixed;
```

#### `Input::getPostRaw()`

`$_GET` -> `$_POST` -> `php://input`

```php
public function getPostRaw(string $key, mixed $default = null, ?array $validation = null): mixed;
```

#### `Input::getRawPost()`

`$_GET` -> `php://input` -> `$_POST`

```php
public function getRawPost(string $key, mixed $default = null, ?array $validation = null): mixed;
```

#### `Input::postGet()`

`$_POST` -> `$_GET`

```php
public function postGet(string $key, mixed $default = null, ?array $validation = null): mixed;
```

#### `Input::postRaw()`

`$_POST` -> `php://input`

```php
public function postRaw(string $key, mixed $default = null, ?array $validation = null): mixed;
```

#### `Input::postGetRaw()`

`$_POST` -> `$_GET` -> `php://input`

```php
public function postGetRaw(string $key, mixed $default = null, ?array $validation = null): mixed;
```

#### `Input::postRawGet()`

`$_POST` -> `php://input` -> `$_GET`

```php
public function postRawGet(string $key, mixed $default = null, ?array $validation = null): mixed;
```

#### `Input::rawGet()`

`php://input` -> `$_GET`

```php
public function rawGet(string $key, mixed $default = null, ?array $validation = null): mixed;
```

#### `Input::rawPost()`

`php://input` -> `$_POST`

```php
public function rawPost(string $key, mixed $default = null, ?array $validation = null): mixed;
```

#### `Input::rawGetPost()`

`php://input` -> `$_GET` -> `$_POST`

```php
public function rawGetPost(string $key, mixed $default = null, ?array $validation = null): mixed;
```

#### `Input::rawPostGet()`

`php://input` -> `$_POST` -> `$_GET`

```php
public function rawPostGet(string $key, mixed $default = null, ?array $validation = null): mixed;
```

### Has it been declared?

Checks to see if the requested entry has been declared.

#### `Input::hasGet()`

It does something like `isset($_GET['key'])` , case-insensitively.

```php
public function hasGet(string $key): bool;
```

#### `Input::hasPost()`

It does something like `isset($_POST['key'])` , case-insensitively.

```php
public function hasPost(string $key): bool;
```

#### `Input::hasRaw()`

Case-insensitively, it queries the body inputs for a key value.

```php
public function hasRaw(string $key): bool;
```

#### `Input::hasFiles()`

It does something like `isset($_FILES['key'])` , case-insensitively.

```php
public function hasFiles(string $key): bool;
```

## Credits

- [Muhammet ŞAFAK](https://www.muhammetsafak.com.tr) <<info@muhammetsafak.com.tr>>

## License

Copyright &copy; 2022 [MIT License](./LICENSE)
