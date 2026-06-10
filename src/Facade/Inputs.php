<?php

/**
 * This file is part of the initphp/input package.
 *
 * (c) Muhammet ŞAFAK <info@muhammetsafak.com.tr>
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 *
 * @link https://github.com/InitPHP/Input
 */

declare(strict_types=1);

namespace InitPHP\Input\Facade;

use InitPHP\Input\InputInterface;
use InitPHP\Input\Inputs as InputsInstance;

use function array_values;

/**
 * Static proxy (facade) over a single {@see InputInterface} instance.
 *
 * The backing instance is created lazily from the PHP superglobals on the
 * first call and reused for the rest of the request. Inject a
 * pre-configured instance with {@see self::setInstance()} (handy in tests
 * or when the request data comes from somewhere other than the globals)
 * and drop it again with {@see self::reset()}.
 *
 * @method static mixed get(string $key, mixed $default = null, array<int, string|callable>|null $validation = null)
 * @method static mixed post(string $key, mixed $default = null, array<int, string|callable>|null $validation = null)
 * @method static mixed raw(string $key, mixed $default = null, array<int, string|callable>|null $validation = null)
 * @method static mixed getPost(string $key, mixed $default = null, array<int, string|callable>|null $validation = null)
 * @method static mixed getRaw(string $key, mixed $default = null, array<int, string|callable>|null $validation = null)
 * @method static mixed getPostRaw(string $key, mixed $default = null, array<int, string|callable>|null $validation = null)
 * @method static mixed getRawPost(string $key, mixed $default = null, array<int, string|callable>|null $validation = null)
 * @method static mixed postGet(string $key, mixed $default = null, array<int, string|callable>|null $validation = null)
 * @method static mixed postRaw(string $key, mixed $default = null, array<int, string|callable>|null $validation = null)
 * @method static mixed postGetRaw(string $key, mixed $default = null, array<int, string|callable>|null $validation = null)
 * @method static mixed postRawGet(string $key, mixed $default = null, array<int, string|callable>|null $validation = null)
 * @method static mixed rawGet(string $key, mixed $default = null, array<int, string|callable>|null $validation = null)
 * @method static mixed rawPost(string $key, mixed $default = null, array<int, string|callable>|null $validation = null)
 * @method static mixed rawGetPost(string $key, mixed $default = null, array<int, string|callable>|null $validation = null)
 * @method static mixed rawPostGet(string $key, mixed $default = null, array<int, string|callable>|null $validation = null)
 * @method static bool  hasGet(string $key)
 * @method static bool  hasPost(string $key)
 * @method static bool  hasRaw(string $key)
 *
 * @see InputInterface
 */
final class Inputs
{
    /**
     * The backing instance, or null until the first access.
     */
    private static ?InputInterface $instance = null;

    /**
     * Replace the backing instance.
     *
     * Mainly useful for tests and for wiring a custom-built
     * {@see InputInterface} (for example one seeded with explicit data)
     * behind the facade.
     */
    public static function setInstance(InputInterface $instance): void
    {
        self::$instance = $instance;
    }

    /**
     * Forget the backing instance so the next call rebuilds it from the
     * current superglobals / request body.
     */
    public static function reset(): void
    {
        self::$instance = null;
    }

    /**
     * Proxy a static call to the backing instance.
     *
     * @param string            $name      The accessor name.
     * @param array<int, mixed> $arguments Positional arguments.
     *
     * @return mixed
     */
    public static function __callStatic(string $name, array $arguments): mixed
    {
        return self::getInstance()->{$name}(...array_values($arguments));
    }

    /**
     * Return the backing instance, creating it from the superglobals on
     * first use.
     */
    private static function getInstance(): InputInterface
    {
        if (self::$instance === null) {
            self::$instance = new InputsInstance();
        }

        return self::$instance;
    }
}
