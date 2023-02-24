# InitPHP Input

Is a library for prioritizing or verifying Get, Post and Raw inputs.

[![Latest Stable Version](http://poser.pugx.org/initphp/input/v)](https://packagist.org/packages/initphp/input) [![Total Downloads](http://poser.pugx.org/initphp/input/downloads)](https://packagist.org/packages/initphp/input) [![Latest Unstable Version](http://poser.pugx.org/initphp/input/v/unstable)](https://packagist.org/packages/initphp/input) [![License](http://poser.pugx.org/initphp/input/license)](https://packagist.org/packages/initphp/input) [![PHP Version Require](http://poser.pugx.org/initphp/input/require/php)](https://packagist.org/packages/initphp/input)


## Requirements

- PHP 7.2 or later
- [InitPHP ParameterBag](https://github.com/InitPHP/ParameterBag)
- [InitPHP Validation](https://github.com/InitPHP/Validation)

## Installation

```
composer require initphp/input
```

## Usage

**Example :**

```php
require_once "vendor/autoload.php";
use \InitPHP\Input\Facade\Inputs as Input;

// echo isset($_GET['name']) ? $_GET['name'] : 'John';
echo Input::get('name', 'John');
```

**Example :**

```php
require_once "vendor/autoload.php";
use \InitPHP\Input\Facade\Inputs as Input;

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

**Example :**

```php
require_once "vendor/autoload.php";
use \InitPHP\Input\Facade\Inputs as Input;

/**
 * if(isset($_POST['password']) && isset($_POST['password_retype']) && !empty($_POST['password']) && $_POST['password'] == $_POST['password_retype']){
 *      $password = $_POST['password'];
 * }else{
 *      $password = null;
 * }
 */
 
$password = Input::post('password', null, ['required', 'again(password_retype)'])
```

## Methods

#### `Inputs::get()`

```php
public function get(string $key, mixed $default = null, ?array $validation = null): mixed;
```

#### `Inputs::post()`

```php
public function post(string $key, mixed $default = null, ?array $validation = null): mixed;
```

#### `Inputs::raw()`

Data from reading `php://input`.

```php
public function raw(string $key, mixed $default = null, ?array $validation = null): mixed;
```

### Getting Input with Priority

#### `Inputs::getPost()`

`$_GET` -> `$_POST`

```php
public function getPost(string $key, mixed $default = null, ?array $validation = null): mixed;
```

#### `Inputs::getRaw()`

`$_GET` -> `php://input`

```php
public function getRaw(string $key, mixed $default = null, ?array $validation = null): mixed;
```

#### `Inputs::getPostRaw()`

`$_GET` -> `$_POST` -> `php://input`

```php
public function getPostRaw(string $key, mixed $default = null, ?array $validation = null): mixed;
```

#### `Inputs::getRawPost()`

`$_GET` -> `php://input` -> `$_POST`

```php
public function getRawPost(string $key, mixed $default = null, ?array $validation = null): mixed;
```

#### `Inputs::postGet()`

`$_POST` -> `$_GET`

```php
public function postGet(string $key, mixed $default = null, ?array $validation = null): mixed;
```

#### `Inputs::postRaw()`

`$_POST` -> `php://input`

```php
public function postRaw(string $key, mixed $default = null, ?array $validation = null): mixed;
```

#### `Inputs::postGetRaw()`

`$_POST` -> `$_GET` -> `php://input`

```php
public function postGetRaw(string $key, mixed $default = null, ?array $validation = null): mixed;
```

#### `Inputs::postRawGet()`

`$_POST` -> `php://input` -> `$_GET`

```php
public function postRawGet(string $key, mixed $default = null, ?array $validation = null): mixed;
```

#### `Inputs::rawGet()`

`php://input` -> `$_GET`

```php
public function rawGet(string $key, mixed $default = null, ?array $validation = null): mixed;
```

#### `Inputs::rawPost()`

`php://input` -> `$_POST`

```php
public function rawPost(string $key, mixed $default = null, ?array $validation = null): mixed;
```

#### `Inputs::rawGetPost()`

`php://input` -> `$_GET` -> `$_POST`

```php
public function rawGetPost(string $key, mixed $default = null, ?array $validation = null): mixed;
```

#### `Inputs::rawPostGet()`

`php://input` -> `$_POST` -> `$_GET`

```php
public function rawPostGet(string $key, mixed $default = null, ?array $validation = null): mixed;
```

### Has it been declared?

Checks to see if the requested entry has been declared.

#### `Inputs::hasGet()`

It does something like `isset($_GET['key'])` , case-insensitively.

```php
public function hasGet(string $key): bool;
```

#### `Inputs::hasPost()`

It does something like `isset($_POST['key'])` , case-insensitively.

```php
public function hasPost(string $key): bool;
```

#### `Inputs::hasRaw()`

Case-insensitively, it queries the body inputs for a key value.

```php
public function hasRaw(string $key): bool;
```

## Credits

- [Muhammet ŞAFAK](https://www.muhammetsafak.com.tr) <<info@muhammetsafak.com.tr>>

## License

Copyright &copy; 2022 [MIT License](./LICENSE)
