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

namespace InitPHP\Input\Tests;

use InitPHP\Input\InputInterface;
use InitPHP\Input\Inputs;
use PHPUnit\Framework\TestCase;

final class InputsTest extends TestCase
{
    public function testImplementsTheInterface(): void
    {
        self::assertInstanceOf(InputInterface::class, new Inputs([], [], []));
    }

    public function testGetReturnsValueFromQueryString(): void
    {
        $input = new Inputs(['name' => 'Alice'], [], []);

        self::assertSame('Alice', $input->get('name'));
    }

    public function testGetReturnsDefaultWhenKeyIsMissing(): void
    {
        $input = new Inputs([], [], []);

        self::assertSame('John', $input->get('name', 'John'));
        self::assertNull($input->get('name'));
    }

    public function testPostAndRawReadTheirOwnSource(): void
    {
        $input = new Inputs([], ['email' => 'a@b.c'], ['token' => 'xyz']);

        self::assertSame('a@b.c', $input->post('email'));
        self::assertSame('xyz', $input->raw('token'));
        self::assertSame('miss', $input->post('token', 'miss'));
        self::assertSame('miss', $input->raw('email', 'miss'));
    }

    public function testHasMethodsReportPresencePerSource(): void
    {
        $input = new Inputs(['g' => 1], ['p' => 2], ['r' => 3]);

        self::assertTrue($input->hasGet('g'));
        self::assertFalse($input->hasGet('p'));
        self::assertTrue($input->hasPost('p'));
        self::assertFalse($input->hasPost('r'));
        self::assertTrue($input->hasRaw('r'));
        self::assertFalse($input->hasRaw('g'));
    }

    public function testHasIsTrueEvenWhenStoredValueIsNull(): void
    {
        $input = new Inputs(['nullable' => null], [], []);

        self::assertTrue($input->hasGet('nullable'));
        self::assertNull($input->get('nullable', 'default'));
    }

    public function testKeysAreMatchedCaseSensitively(): void
    {
        $input = new Inputs(['Name' => 'Alice'], [], []);

        self::assertTrue($input->hasGet('Name'));
        self::assertFalse($input->hasGet('name'));
        self::assertSame('Alice', $input->get('Name', 'default'));
        self::assertSame('default', $input->get('name', 'default'));
    }

    /**
     * @dataProvider priorityProvider
     *
     * @param array<string, mixed> $get
     * @param array<string, mixed> $post
     * @param array<string, mixed> $raw
     */
    public function testPriorityResolution(string $method, array $get, array $post, array $raw, string $expected): void
    {
        $input = new Inputs($get, $post, $raw);

        self::assertSame($expected, $input->{$method}('k', 'default'));
    }

    /**
     * Each row asserts that the helper returns the value of the first
     * source (in the order its name reads) that contains the key, and
     * that absent sources are skipped.
     *
     * @return array<string, array{0: string, 1: array<string, mixed>, 2: array<string, mixed>, 3: array<string, mixed>, 4: string}>
     */
    public static function priorityProvider(): array
    {
        $g = ['k' => 'G'];
        $p = ['k' => 'P'];
        $r = ['k' => 'R'];
        $e = [];

        return [
            'getPost picks get'        => ['getPost', $g, $p, $r, 'G'],
            'getPost falls to post'    => ['getPost', $e, $p, $e, 'P'],
            'getRaw picks get'         => ['getRaw', $g, $p, $r, 'G'],
            'getRaw falls to raw'      => ['getRaw', $e, $e, $r, 'R'],
            'getPostRaw picks get'     => ['getPostRaw', $g, $p, $r, 'G'],
            'getPostRaw falls to raw'  => ['getPostRaw', $e, $e, $r, 'R'],
            'getRawPost picks get'     => ['getRawPost', $g, $p, $r, 'G'],
            'getRawPost falls to post' => ['getRawPost', $e, $p, $e, 'P'],
            'postGet picks post'       => ['postGet', $g, $p, $r, 'P'],
            'postGet falls to get'     => ['postGet', $g, $e, $e, 'G'],
            'postRaw picks post'       => ['postRaw', $g, $p, $r, 'P'],
            'postRaw falls to raw'     => ['postRaw', $e, $e, $r, 'R'],
            'postGetRaw picks post'    => ['postGetRaw', $g, $p, $r, 'P'],
            'postGetRaw falls to raw'  => ['postGetRaw', $e, $e, $r, 'R'],
            'postRawGet picks post'    => ['postRawGet', $g, $p, $r, 'P'],
            'postRawGet falls to get'  => ['postRawGet', $g, $e, $e, 'G'],
            'rawGet picks raw'         => ['rawGet', $g, $p, $r, 'R'],
            'rawGet falls to get'      => ['rawGet', $g, $e, $e, 'G'],
            'rawPost picks raw'        => ['rawPost', $g, $p, $r, 'R'],
            'rawPost falls to post'    => ['rawPost', $e, $p, $e, 'P'],
            'rawGetPost picks raw'     => ['rawGetPost', $g, $p, $r, 'R'],
            'rawGetPost falls to post' => ['rawGetPost', $e, $p, $e, 'P'],
            'rawPostGet picks raw'     => ['rawPostGet', $g, $p, $r, 'R'],
            'rawPostGet falls to get'  => ['rawPostGet', $g, $e, $e, 'G'],
        ];
    }

    public function testPriorityReturnsDefaultWhenNoSourceHasTheKey(): void
    {
        $input = new Inputs([], [], []);

        self::assertSame('default', $input->getPostRaw('k', 'default'));
        self::assertSame('default', $input->rawPostGet('k', 'default'));
    }

    public function testTwoLiveInstancesDoNotShareState(): void
    {
        $a = new Inputs(['k' => 'A'], [], []);
        $b = new Inputs(['k' => 'B'], [], []);

        self::assertSame('A', $a->get('k'));
        self::assertSame('B', $b->get('k'));
    }

    public function testDestroyingAnInstanceDoesNotBreakLaterInstances(): void
    {
        // Regression: the previous static-state design wiped shared bags in
        // __destruct, leaving every subsequent instance permanently empty.
        $a = new Inputs(['k' => 'A'], [], []);
        self::assertSame('A', $a->get('k'));
        unset($a);

        $b = new Inputs([], [], []);
        self::assertSame('fallback', $b->get('k', 'fallback'));

        $c = new Inputs(['k' => 'C'], [], []);
        self::assertSame('C', $c->get('k'));
    }

    public function testReadsFromSuperglobalsWhenArgumentsOmitted(): void
    {
        $originalGet = $_GET;
        $originalPost = $_POST;
        $_GET = ['sg' => 'from-get'];
        $_POST = ['sp' => 'from-post'];

        try {
            $input = new Inputs();

            self::assertSame('from-get', $input->get('sg'));
            self::assertSame('from-post', $input->post('sp'));
            self::assertSame('none', $input->raw('anything', 'none'));
        } finally {
            $_GET = $originalGet;
            $_POST = $originalPost;
        }
    }
}
