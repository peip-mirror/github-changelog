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

namespace Localheinz\GitHub\ChangeLog\Exception;

final class RangeNotFound extends \RuntimeException implements ExceptionInterface
{
    public static function fromOwnerRepositoryAndReferences(string $owner, string $repository, string $startReference, string $endReference): self
    {
        return new self(\sprintf(
            'Could not find a range of commits between "%s" and "%s" in "%s/%s".',
            $startReference,
            $endReference,
            $owner,
            $repository
        ));
    }
}
