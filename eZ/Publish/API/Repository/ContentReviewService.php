<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository;

use eZ\Publish\API\Repository\Values\Content\VersionInfo;
use eZ\Publish\API\Repository\Values\ContentReview\CommentCreateStruct;
use eZ\Publish\API\Repository\Values\ContentReview\ContentReview;
use eZ\Publish\API\Repository\Values\ContentReview\CreateStruct;
use eZ\Publish\API\Repository\Values\ContentReview\UpdateStruct;
use eZ\Publish\API\Repository\Values\User\User;

/**
 * Content Review Service
 */
interface ContentReviewService
{
    /**
     * Creates a review of content.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\VersionInfo $versionInfo
     * @param \eZ\Publish\API\Repository\Values\ContentReview\CreateStruct $createStruct
     *
     * @return \eZ\Publish\API\Repository\Values\ContentReview\ContentReview
     */
    public function createReview(VersionInfo $versionInfo, CreateStruct $createStruct): ContentReview;

    /**
     * Updates review.
     *
     * @param \eZ\Publish\API\Repository\Values\ContentReview\ContentReview $review
     * @param \eZ\Publish\API\Repository\Values\ContentReview\UpdateStruct $updateStruct
     */
    public function updateReview(ContentReview $review, UpdateStruct $updateStruct);

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\VersionInfo $versionInfo
     * @param \eZ\Publish\API\Repository\Values\User\User|null $user
     *
     * @return \eZ\Publish\API\Repository\Values\ContentReview\ContentReview
     */
    public function loadReviewByUser(VersionInfo $versionInfo, User $user = null): ContentReview;

    /**
     * Loads content reviews.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\VersionInfo $versionInfo
     * @return \eZ\Publish\API\Repository\Values\ContentReview\ContentReview[]
     */
    public function loadReviews(VersionInfo $versionInfo): array;

    /**
     * Adds comment to review.
     *
     * @param \eZ\Publish\API\Repository\Values\ContentReview\ContentReview $review
     * @param \eZ\Publish\API\Repository\Values\ContentReview\CommentCreateStruct $createStruct
     */
    public function createComment(ContentReview $review, CommentCreateStruct $createStruct): void;
}
