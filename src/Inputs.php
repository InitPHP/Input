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

namespace InitPHP\Input;

use InitPHP\ParameterBag\ParameterBag;
use InitPHP\Validation\Validation;

use function file_get_contents;
use function is_array;
use function json_decode;

/**
 * Default {@see InputInterface} implementation.
 *
 * Each source is wrapped in its own flat {@see ParameterBag}. By default
 * the bags are populated from the PHP superglobals (`$_GET`, `$_POST`)
 * and the JSON request body (`php://input`); every source can instead be
 * supplied explicitly through the constructor, which makes the class
 * trivial to unit test without touching global state.
 *
 * Unlike a previous design, no state is shared statically between
 * instances: two `Inputs` objects never interfere with one another.
 *
 * @see InputInterface
 */
final class Inputs implements InputInterface
{
    /**
     * Source identifier for the query string (`$_GET`).
     */
    private const SOURCE_GET = 'get';

    /**
     * Source identifier for the submitted form fields (`$_POST`).
     */
    private const SOURCE_POST = 'post';

    /**
     * Source identifier for the decoded JSON request body.
     */
    private const SOURCE_RAW = 'raw';

    /**
     * The per-source parameter bags, keyed by source identifier.
     *
     * @var array<string, ParameterBag>
     */
    private array $bags;

    /**
     * The validator used to check values against caller-supplied rules.
     */
    private Validation $validation;

    /**
     * @param array<array-key, mixed>|null $get        Query parameters.
     *                                                 Defaults to `$_GET`.
     * @param array<array-key, mixed>|null $post       Form parameters.
     *                                                 Defaults to `$_POST`.
     * @param array<array-key, mixed>|null $raw        Decoded JSON body.
     *                                                 Defaults to the
     *                                                 decoded `php://input`
     *                                                 payload.
     * @param Validation|null              $validation Validator instance.
     *                                                 A fresh one is
     *                                                 created when omitted.
     */
    public function __construct(
        ?array $get = null,
        ?array $post = null,
        ?array $raw = null,
        ?Validation $validation = null
    ) {
        $options = ['isMulti' => false];
        $this->bags = [
            self::SOURCE_GET  => new ParameterBag($get ?? $_GET, $options),
            self::SOURCE_POST => new ParameterBag($post ?? $_POST, $options),
            self::SOURCE_RAW  => new ParameterBag($raw ?? self::decodeJsonBody(self::readRawInput()), $options),
        ];
        $this->validation = $validation ?? new Validation();
    }

    /**
     * {@inheritDoc}
     */
    public function get(string $key, mixed $default = null, ?array $validation = null): mixed
    {
        return $this->resolve([self::SOURCE_GET], $key, $default, $validation);
    }

    /**
     * {@inheritDoc}
     */
    public function post(string $key, mixed $default = null, ?array $validation = null): mixed
    {
        return $this->resolve([self::SOURCE_POST], $key, $default, $validation);
    }

    /**
     * {@inheritDoc}
     */
    public function raw(string $key, mixed $default = null, ?array $validation = null): mixed
    {
        return $this->resolve([self::SOURCE_RAW], $key, $default, $validation);
    }

    /**
     * {@inheritDoc}
     */
    public function getPost(string $key, mixed $default = null, ?array $validation = null): mixed
    {
        return $this->resolve([self::SOURCE_GET, self::SOURCE_POST], $key, $default, $validation);
    }

    /**
     * {@inheritDoc}
     */
    public function getRaw(string $key, mixed $default = null, ?array $validation = null): mixed
    {
        return $this->resolve([self::SOURCE_GET, self::SOURCE_RAW], $key, $default, $validation);
    }

    /**
     * {@inheritDoc}
     */
    public function getPostRaw(string $key, mixed $default = null, ?array $validation = null): mixed
    {
        return $this->resolve(
            [self::SOURCE_GET, self::SOURCE_POST, self::SOURCE_RAW],
            $key,
            $default,
            $validation
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getRawPost(string $key, mixed $default = null, ?array $validation = null): mixed
    {
        return $this->resolve(
            [self::SOURCE_GET, self::SOURCE_RAW, self::SOURCE_POST],
            $key,
            $default,
            $validation
        );
    }

    /**
     * {@inheritDoc}
     */
    public function postGet(string $key, mixed $default = null, ?array $validation = null): mixed
    {
        return $this->resolve([self::SOURCE_POST, self::SOURCE_GET], $key, $default, $validation);
    }

    /**
     * {@inheritDoc}
     */
    public function postRaw(string $key, mixed $default = null, ?array $validation = null): mixed
    {
        return $this->resolve([self::SOURCE_POST, self::SOURCE_RAW], $key, $default, $validation);
    }

    /**
     * {@inheritDoc}
     */
    public function postGetRaw(string $key, mixed $default = null, ?array $validation = null): mixed
    {
        return $this->resolve(
            [self::SOURCE_POST, self::SOURCE_GET, self::SOURCE_RAW],
            $key,
            $default,
            $validation
        );
    }

    /**
     * {@inheritDoc}
     */
    public function postRawGet(string $key, mixed $default = null, ?array $validation = null): mixed
    {
        return $this->resolve(
            [self::SOURCE_POST, self::SOURCE_RAW, self::SOURCE_GET],
            $key,
            $default,
            $validation
        );
    }

    /**
     * {@inheritDoc}
     */
    public function rawGet(string $key, mixed $default = null, ?array $validation = null): mixed
    {
        return $this->resolve([self::SOURCE_RAW, self::SOURCE_GET], $key, $default, $validation);
    }

    /**
     * {@inheritDoc}
     */
    public function rawPost(string $key, mixed $default = null, ?array $validation = null): mixed
    {
        return $this->resolve([self::SOURCE_RAW, self::SOURCE_POST], $key, $default, $validation);
    }

    /**
     * {@inheritDoc}
     */
    public function rawGetPost(string $key, mixed $default = null, ?array $validation = null): mixed
    {
        return $this->resolve(
            [self::SOURCE_RAW, self::SOURCE_GET, self::SOURCE_POST],
            $key,
            $default,
            $validation
        );
    }

    /**
     * {@inheritDoc}
     */
    public function rawPostGet(string $key, mixed $default = null, ?array $validation = null): mixed
    {
        return $this->resolve(
            [self::SOURCE_RAW, self::SOURCE_POST, self::SOURCE_GET],
            $key,
            $default,
            $validation
        );
    }

    /**
     * {@inheritDoc}
     */
    public function hasGet(string $key): bool
    {
        return $this->bags[self::SOURCE_GET]->has($key);
    }

    /**
     * {@inheritDoc}
     */
    public function hasPost(string $key): bool
    {
        return $this->bags[self::SOURCE_POST]->has($key);
    }

    /**
     * {@inheritDoc}
     */
    public function hasRaw(string $key): bool
    {
        return $this->bags[self::SOURCE_RAW]->has($key);
    }

    /**
     * Walk $sources in order and return the value of the first source
     * that contains $key.
     *
     * Once a source owns the key the lookup stops: when $validation is
     * supplied and the value fails it, $default is returned rather than
     * continuing to the next source.
     *
     * @param list<string>                     $sources    Ordered source
     *                                                     identifiers.
     * @param array<int, string|callable>|null $validation Rules for the
     *                                                     resolved value.
     *
     * @return mixed The resolved value, or $default.
     */
    private function resolve(array $sources, string $key, mixed $default, ?array $validation): mixed
    {
        foreach ($sources as $source) {
            $bag = $this->bags[$source];
            if (!$bag->has($key)) {
                continue;
            }
            $value = $bag->get($key, $default);
            if (empty($validation)) {
                return $value;
            }

            return $this->isValid($bag, $key, $validation) ? $value : $default;
        }

        return $default;
    }

    /**
     * Validate the value stored under $key against $rules, using the
     * whole bag as the data set so cross-field rules (e.g.
     * `again(field)`) can resolve their siblings.
     *
     * @param array<int, string|callable> $rules
     */
    private function isValid(ParameterBag $bag, string $key, array $rules): bool
    {
        $data = [];
        foreach ($bag->all() as $name => $value) {
            $data[(string) $name] = $value;
        }
        $this->validation->setData($data);
        $this->validation->rule($key, $rules);

        return $this->validation->validation();
    }

    /**
     * Decode a raw JSON request body into an input array.
     *
     * A body that is empty or whose JSON is not an object/array (a scalar
     * such as `5`, `"x"` or `true`, or invalid JSON) yields an empty
     * array, so a scalar payload can never reach the {@see ParameterBag}
     * constructor (which would raise a TypeError).
     *
     * @return array<array-key, mixed>
     */
    public static function decodeJsonBody(string $body): array
    {
        if ($body === '') {
            return [];
        }
        $decoded = json_decode($body, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Read the raw request body from `php://input`, normalising a read
     * failure to an empty string.
     */
    private static function readRawInput(): string
    {
        $body = file_get_contents('php://input');

        return $body === false ? '' : $body;
    }
}
