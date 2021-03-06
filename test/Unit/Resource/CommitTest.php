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
final class CommitTest extends Framework\TestCase
{
    use Helper;

    public function testImplementsAuthorInterface(): void
    {
        $this->assertClassImplementsInterface(Resource\CommitInterface::class, Resource\Commit::class);
    }

    /**
     * @dataProvider \Localheinz\GitHub\ChangeLog\Test\Util\DataProvider::providerInvalidSha
     *
     * @param string $sha
     */
    public function testConstructorRejectsInvalidSha(string $sha): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage(\sprintf(
            'Sha "%s" does not appear to be a valid sha1 hash.',
            $sha
        ));

        $message = $this->faker()->sentence();

        new Resource\Commit(
            $sha,
            $message
        );
    }

    public function testConstructorSetsShaAndMessage(): void
    {
        $faker = $this->faker();

        $sha = $faker->sha1;
        $message = $faker->sentence();

        $commit = new Resource\Commit(
            $sha,
            $message
        );

        self::assertSame($sha, $commit->sha());
        self::assertSame($message, $commit->message());
    }

    public function testEqualsReturnsFalseIfHashesAreDifferent(): void
    {
        $faker = $this->faker();

        $one = new Resource\Commit(
            $faker->unique()->sha1,
            $faker->unique()->sentence()
        );

        $two = new Resource\Commit(
            $faker->unique()->sha1,
            $faker->unique()->sentence()
        );

        self::assertFalse($one->equals($two));
    }

    public function testEqualsReturnsTrueIfHashesAreTheSame(): void
    {
        $faker = $this->faker();

        $sha = $faker->sha1;

        $one = new Resource\Commit(
            $sha,
            $faker->unique()->sentence()
        );

        $two = new Resource\Commit(
            $sha,
            $faker->unique()->sentence()
        );

        self::assertTrue($one->equals($two));
    }
}
