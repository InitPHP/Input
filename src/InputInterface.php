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

/**
 * Reads a single value from one or more request input sources.
 *
 * Three sources are recognised:
 *
 *  - `get`  — the query string (`$_GET`).
 *  - `post` — submitted form fields (`$_POST`).
 *  - `raw`  — the decoded JSON request body (`php://input`).
 *
 * The priority helpers (e.g. {@see self::getPost()}) walk their sources
 * in the order their name reads, left to right. The first source that
 * **contains** the key wins; the remaining sources are never consulted,
 * even when the chosen value fails validation. A value that is present
 * but invalid yields the supplied default rather than falling through to
 * the next source.
 *
 * Keys are matched case-sensitively, mirroring how HTTP query and body
 * parameters behave.
 *
 * The optional `$validation` argument of every accessor is a list of
 * rules understood by {@see \InitPHP\Validation\Validation::rule()} — a
 * DSL string such as `'required'` or `'range(1...10)'`, or a callable.
 * When the list is empty or null, no validation is performed.
 */
interface InputInterface
{
    /**
     * Read $key from the query string (`$_GET`).
     *
     * @param array<int, string|callable>|null $validation Rules to apply
     *                                                     to the value.
     *
     * @return mixed The value, or $default when the key is absent or the
     *               value fails validation.
     */
    public function get(string $key, mixed $default = null, ?array $validation = null): mixed;

    /**
     * Read $key from the submitted form fields (`$_POST`).
     *
     * @param array<int, string|callable>|null $validation
     *
     * @return mixed
     */
    public function post(string $key, mixed $default = null, ?array $validation = null): mixed;

    /**
     * Read $key from the decoded JSON request body (`php://input`).
     *
     * @param array<int, string|callable>|null $validation
     *
     * @return mixed
     */
    public function raw(string $key, mixed $default = null, ?array $validation = null): mixed;

    /**
     * Read $key from `$_GET`, falling back to `$_POST`.
     *
     * @param array<int, string|callable>|null $validation
     *
     * @return mixed
     */
    public function getPost(string $key, mixed $default = null, ?array $validation = null): mixed;

    /**
     * Read $key from `$_GET`, falling back to the JSON body.
     *
     * @param array<int, string|callable>|null $validation
     *
     * @return mixed
     */
    public function getRaw(string $key, mixed $default = null, ?array $validation = null): mixed;

    /**
     * Read $key from `$_GET`, then `$_POST`, then the JSON body.
     *
     * @param array<int, string|callable>|null $validation
     *
     * @return mixed
     */
    public function getPostRaw(string $key, mixed $default = null, ?array $validation = null): mixed;

    /**
     * Read $key from `$_GET`, then the JSON body, then `$_POST`.
     *
     * @param array<int, string|callable>|null $validation
     *
     * @return mixed
     */
    public function getRawPost(string $key, mixed $default = null, ?array $validation = null): mixed;

    /**
     * Read $key from `$_POST`, falling back to `$_GET`.
     *
     * @param array<int, string|callable>|null $validation
     *
     * @return mixed
     */
    public function postGet(string $key, mixed $default = null, ?array $validation = null): mixed;

    /**
     * Read $key from `$_POST`, falling back to the JSON body.
     *
     * @param array<int, string|callable>|null $validation
     *
     * @return mixed
     */
    public function postRaw(string $key, mixed $default = null, ?array $validation = null): mixed;

    /**
     * Read $key from `$_POST`, then `$_GET`, then the JSON body.
     *
     * @param array<int, string|callable>|null $validation
     *
     * @return mixed
     */
    public function postGetRaw(string $key, mixed $default = null, ?array $validation = null): mixed;

    /**
     * Read $key from `$_POST`, then the JSON body, then `$_GET`.
     *
     * @param array<int, string|callable>|null $validation
     *
     * @return mixed
     */
    public function postRawGet(string $key, mixed $default = null, ?array $validation = null): mixed;

    /**
     * Read $key from the JSON body, falling back to `$_GET`.
     *
     * @param array<int, string|callable>|null $validation
     *
     * @return mixed
     */
    public function rawGet(string $key, mixed $default = null, ?array $validation = null): mixed;

    /**
     * Read $key from the JSON body, falling back to `$_POST`.
     *
     * @param array<int, string|callable>|null $validation
     *
     * @return mixed
     */
    public function rawPost(string $key, mixed $default = null, ?array $validation = null): mixed;

    /**
     * Read $key from the JSON body, then `$_GET`, then `$_POST`.
     *
     * @param array<int, string|callable>|null $validation
     *
     * @return mixed
     */
    public function rawGetPost(string $key, mixed $default = null, ?array $validation = null): mixed;

    /**
     * Read $key from the JSON body, then `$_POST`, then `$_GET`.
     *
     * @param array<int, string|callable>|null $validation
     *
     * @return mixed
     */
    public function rawPostGet(string $key, mixed $default = null, ?array $validation = null): mixed;

    /**
     * Whether $key is present in the query string (`$_GET`).
     */
    public function hasGet(string $key): bool;

    /**
     * Whether $key is present in the submitted form fields (`$_POST`).
     */
    public function hasPost(string $key): bool;

    /**
     * Whether $key is present in the decoded JSON request body.
     */
    public function hasRaw(string $key): bool;
}
