<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Legacy\ContentReview;

use eZ\Publish\SPI\Persistence\ContentReview\ContentReview;
use eZ\Publish\SPI\Persistence\ContentReview\Comment;
use eZ\Publish\SPI\Persistence\ContentReview\UpdateStruct;

abstract class Gateway
{
    public abstract function createReview(ContentReview $contentReview): int;

    public abstract function createComment(Comment $comment): int;

    public abstract function loadReviews(int $contentId, int $versionNo): array;

    public abstract function loadReviewComments(int $reviewId): array;

    public abstract function updateReview(UpdateStruct $updateStruct): void;
}
