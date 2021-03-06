<?php

declare(strict_types=1);

/**
 * Copyright (c) 2017 Andreas Möller
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/localheinz/github-changelog
 */

namespace Localheinz\GitHub\ChangeLog\Test\Unit\Resource;

use Localheinz\GitHub\ChangeLog\Exception;
use Localheinz\GitHub\ChangeLog\Resource;
use Localheinz\Test\Util\Helper;
use PHPUnit\Framework;

/**
 * @internal
 */
final class PullRequestTest extends Framework\TestCase
{
    use Helper;

    public function testImplementsPullRequestInterface(): void
    {
        $this->assertClassImplementsInterface(Resource\PullRequestInterface::class, Resource\PullRequest::class);
    }

    /**
     * @dataProvider \Localheinz\GitHub\ChangeLog\Test\Util\DataProvider::providerInvalidPullRequestNumber
     *
     * @param mixed $number
     */
    public function testConstructorRejectsInvalidNumber(int $number): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage(\sprintf(
            'Number "%d" does not appear to be a valid pull request number.',
            $number
        ));

        $faker = $this->faker();

        $title = $faker->sentence();
        $author = new Resource\User($faker->slug());

        new Resource\PullRequest(
            $number,
            $title,
            $author
        );
    }

    public function testConstructorSetsValues(): void
    {
        $faker = $this->faker();

        $number = $faker->numberBetween(1);
        $title = $faker->sentence();
        $author = new Resource\User($faker->slug());

        $pullRequest = new Resource\PullRequest(
            $number,
            $title,
            $author
        );

        self::assertSame($number, $pullRequest->number());
        self::assertSame($title, $pullRequest->title());
        self::assertSame($author, $pullRequest->author());
    }
}
