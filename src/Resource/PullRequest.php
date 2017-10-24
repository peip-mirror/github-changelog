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

namespace Localheinz\GitHub\ChangeLog\Resource;

use Localheinz\GitHub\ChangeLog\Exception;

final class PullRequest implements PullRequestInterface
{
    /**
     * @var int
     */
    private $number;

    /**
     * @var string
     */
    private $title;

    /**
     * @param int    $number
     * @param string $title
     *
     * @throws Exception\InvalidArgumentException
     */
    public function __construct(int $number, string $title)
    {
        if (1 > $number) {
            throw new Exception\InvalidArgumentException(\sprintf(
                'Number "%d" does not appear to be a valid pull request number.',
                $number
            ));
        }

        $this->number = $number;
        $this->title = $title;
    }

    public function number(): int
    {
        return $this->number;
    }

    public function title(): string
    {
        return $this->title;
    }
}
