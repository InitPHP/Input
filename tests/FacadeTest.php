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

use InitPHP\Input\Facade\Inputs as InputFacade;
use InitPHP\Input\Inputs;
use PHPUnit\Framework\TestCase;

final class FacadeTest extends TestCase
{
    protected function tearDown(): void
    {
        // Never let one test's backing instance bleed into the next.
        InputFacade::reset();
    }

    public function testProxiesStaticCallsToTheInjectedInstance(): void
    {
        InputFacade::setInstance(new Inputs(['name' => 'Alice'], ['age' => '30'], []));

        self::assertSame('Alice', InputFacade::get('name'));
        self::assertSame('30', InputFacade::post('age'));
        self::assertTrue(InputFacade::hasGet('name'));
        self::assertFalse(InputFacade::hasPost('name'));
    }

    public function testForwardsValidationArguments(): void
    {
        InputFacade::setInstance(new Inputs(['year' => '3000'], [], []));

        self::assertSame(2015, InputFacade::get('year', 2015, ['range(1970...2070)']));
    }

    public function testResetForgetsTheBackingInstance(): void
    {
        InputFacade::setInstance(new Inputs(['name' => 'Alice'], [], []));
        self::assertSame('Alice', InputFacade::get('name'));

        InputFacade::reset();
        InputFacade::setInstance(new Inputs(['name' => 'Bob'], [], []));

        self::assertSame('Bob', InputFacade::get('name'));
    }

    public function testLazilyBuildsAnInstanceFromSuperglobals(): void
    {
        InputFacade::reset();
        $originalGet = $_GET;
        $_GET = ['facade_key' => 'facade_value'];

        try {
            self::assertSame('facade_value', InputFacade::get('facade_key'));
        } finally {
            $_GET = $originalGet;
            InputFacade::reset();
        }
    }
}
