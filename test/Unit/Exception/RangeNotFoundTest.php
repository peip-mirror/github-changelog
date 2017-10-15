<?php

declare(strict_types=1);

/**
 * Copyright (c) 2017 Andreas Möller.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @link https://github.com/localheinz/github-changelog
 */

namespace Localheinz\GitHub\ChangeLog\Test\Unit\Exception;

use Localheinz\GitHub\ChangeLog\Exception\ExceptionInterface;
use Localheinz\GitHub\ChangeLog\Exception\RangeNotFound;
use PHPUnit\Framework;
use Refinery29\Test\Util\TestHelper;

final class RangeNotFoundTest extends Framework\TestCase
{
    use TestHelper;

    public function testIsFinal()
    {
        $this->assertFinal(RangeNotFound::class);
    }

    public function testExtendsRuntimeException()
    {
        $this->assertExtends(\RuntimeException::class, RangeNotFound::class);
    }

    public function testImplementsExceptionInterface()
    {
        $this->assertImplements(ExceptionInterface::class, RangeNotFound::class);
    }

    public function testFromOwnerRepositoryAndRangeCreatesException()
    {
        $faker = $this->getFaker();

        $owner = $faker->userName;
        $repository = \implode(
            '-',
            $faker->words()
        );
        $startReference = $faker->sha1;
        $endReference = $faker->sha1;

        $exception = RangeNotFound::fromOwnerRepositoryAndReferences(
            $owner,
            $repository,
            $startReference,
            $endReference
        );

        $this->assertInstanceOf(RangeNotFound::class, $exception);

        $message = \sprintf(
            'Could not find a range of commits between "%s" and "%s" in "%s/%s".',
            $startReference,
            $endReference,
            $owner,
            $repository
        );

        $this->assertSame($message, $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
    }
}
