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

final class ValidationTest extends TestCase
{
    public function testValidValueIsReturned(): void
    {
        $input = new Inputs(['year' => '1990'], [], []);

        self::assertSame('1990', $input->get('year', 2015, ['range(1970...2070)']));
    }

    public function testInvalidValueReturnsDefault(): void
    {
        $input = new Inputs(['year' => '3000'], [], []);

        self::assertSame(2015, $input->get('year', 2015, ['range(1970...2070)']));
    }

    public function testEmptyRuleListSkipsValidation(): void
    {
        $input = new Inputs(['x' => 'anything'], [], []);

        self::assertSame('anything', $input->get('x', null, []));
    }

    public function testNullRuleListSkipsValidation(): void
    {
        $input = new Inputs(['x' => 'anything'], [], []);

        self::assertSame('anything', $input->get('x', null, null));
    }

    public function testInvalidValueDoesNotFallThroughToNextSource(): void
    {
        // GET owns "year" with an invalid value while POST holds a valid one.
        // The first source that HAS the key wins, so the invalid GET value is
        // rejected and the default is returned instead of POST's value.
        $input = new Inputs(['year' => '3000'], ['year' => '1990'], []);

        self::assertSame(2015, $input->getPost('year', 2015, ['range(1970...2070)']));
    }

    public function testValidationFallsThroughOnlyWhenKeyIsAbsent(): void
    {
        // GET does not have "year", so POST owns it and its valid value passes.
        $input = new Inputs([], ['year' => '1990'], []);

        self::assertSame('1990', $input->getPost('year', 2015, ['range(1970...2070)']));
    }

    public function testCrossFieldRulePasses(): void
    {
        $input = new Inputs([], [
            'password'        => 'secret',
            'password_retype' => 'secret',
        ], []);

        self::assertSame(
            'secret',
            $input->post('password', null, ['required', 'again(password_retype)'])
        );
    }

    public function testCrossFieldRuleFails(): void
    {
        $input = new Inputs([], [
            'password'        => 'secret',
            'password_retype' => 'different',
        ], []);

        self::assertNull(
            $input->post('password', null, ['required', 'again(password_retype)'])
        );
    }

    public function testCallableRuleIsSupported(): void
    {
        $input = new Inputs(['age' => '20'], [], []);

        $even = static fn ($value): bool => is_numeric($value) && (int) $value % 2 === 0;

        self::assertSame('20', $input->get('age', null, [$even]));
    }

    public function testSequentialValidationsDoNotLeakState(): void
    {
        // A single shared validator is reused across calls; each run must be
        // independent (no rule or error bleed-through between lookups).
        $input = new Inputs(['a' => '5', 'b' => 'not-a-number'], [], []);

        self::assertSame('5', $input->get('a', 'def', ['integer']));
        self::assertSame('def', $input->get('b', 'def', ['integer']));
        self::assertSame('5', $input->get('a', 'def', ['integer']));
    }
}
