<?php
/**
 * Input.php
 *
 * This file is part of InitPHP.
 *
 * @author     Muhammet ŞAFAK <info@muhammetsafak.com.tr>
 * @copyright  Copyright © 2022 InitPHP
 * @license    http://initphp.github.io/license.txt  MIT
 * @version    1.0.2
 * @link       https://www.muhammetsafak.com.tr
 */

declare(strict_types=1);

namespace InitPHP\Input;

/**
 * @mixin Stack
 * @method static mixed get(string $key, mixed $default = null, ?array $validation = null)
 * @method static mixed getPost(string $key, mixed $default = null, ?array $validation = null)
 * @method static mixed getRaw(string $key, mixed $default = null, ?array $validation = null)
 * @method static mixed getPostRaw(string $key, mixed $default = null, ?array $validation = null)
 * @method static mixed getRawPost(string $key, mixed $default = null, ?array $validation = null)
 * @method static mixed raw(string $key, mixed $default = null, ?array $validation = null)
 * @method static mixed rawGet(string $key, mixed $default = null, ?array $validation = null)
 * @method static mixed rawPost(string $key, mixed $default = null, ?array $validation = null)
 * @method static mixed rawGetPost(string $key, mixed $default = null, ?array $validation = null)
 * @method static mixed rawPostGet(string $key, mixed $default = null, ?array $validation = null)
 * @method static mixed post(string $key, mixed $default = null, ?array $validation = null)
 * @method static mixed postGet(string $key, mixed $default = null, ?array $validation = null)
 * @method static mixed postRaw(string $key, mixed $default = null, ?array $validation = null)
 * @method static mixed postGetRaw(string $key, mixed $default = null, ?array $validation = null)
 * @method static mixed postRawGet(string $key, mixed $default = null, ?array $validation = null))
 * @method static bool hasGet(string $key)
 * @method static bool hasRaw(string $key)
 * @method static bool hasPost(string $key)
 */
final class Input
{

    protected static Stack $stack;

    public function __construct()
    {
        self::getStackInstance();
    }

    public function __destruct()
    {
        if(isset(self::$stack)){
            self::$stack->__destruct();
        }
    }

    public function __call($name, $arguments)
    {
        return self::getStackInstance()->{$name}(...$arguments);
    }

    public static function __callStatic($name, $arguments)
    {
        return self::getStackInstance()->{$name}(...$arguments);
    }

    protected static function getStackInstance(): Stack
    {
        if(!isset(self::$stack)){
            self::$stack = new Stack();
        }
        return self::$stack;
    }

}
