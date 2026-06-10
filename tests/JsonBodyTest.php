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

use InitPHP\Input\Inputs;
use PHPUnit\Framework\TestCase;

final class JsonBodyTest extends TestCase
{
    public function testDecodesJsonObject(): void
    {
        self::assertSame(['a' => 1, 'b' => 'two'], Inputs::decodeJsonBody('{"a":1,"b":"two"}'));
    }

    public function testDecodesJsonList(): void
    {
        self::assertSame([1, 2, 3], Inputs::decodeJsonBody('[1,2,3]'));
    }

    public function testEmptyBodyYieldsEmptyArray(): void
    {
        self::assertSame([], Inputs::decodeJsonBody(''));
    }

    /**
     * @dataProvider nonArrayBodyProvider
     */
    public function testScalarOrInvalidBodyYieldsEmptyArray(string $body): void
    {
        // Guards the TypeError that a scalar JSON payload would otherwise
        // trigger when handed to the ParameterBag constructor.
        self::assertSame([], Inputs::decodeJsonBody($body));
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function nonArrayBodyProvider(): array
    {
        return [
            'integer'      => ['5'],
            'float'        => ['3.14'],
            'string'       => ['"hello"'],
            'true'         => ['true'],
            'false'        => ['false'],
            'null literal' => ['null'],
            'invalid json' => ['{not json}'],
            'bare word'    => ['hello'],
        ];
    }

    public function testRawSourceIsPopulatedFromInjectedArray(): void
    {
        $input = new Inputs([], [], ['user' => 'bob', 'roles' => ['admin']]);

        self::assertSame('bob', $input->raw('user'));
        self::assertSame(['admin'], $input->raw('roles'));
    }

    public function testRawSourceIsEmptyWhenBodyIsAbsent(): void
    {
        // No body is piped to php://input under the test runner, so the raw
        // bag is empty and every lookup returns its default.
        $input = new Inputs([], [], null);

        self::assertSame('default', $input->raw('missing', 'default'));
        self::assertFalse($input->hasRaw('missing'));
    }
}
