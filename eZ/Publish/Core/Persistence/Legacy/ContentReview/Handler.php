<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Legacy\ContentReview;

use eZ\Publish\SPI\Persistence\ContentReview\Comment;
use eZ\Publish\SPI\Persistence\ContentReview\CommentCreateStruct;
use eZ\Publish\SPI\Persistence\ContentReview\ContentReview;
use eZ\Publish\SPI\Persistence\ContentReview\CreateStruct;
use eZ\Publish\SPI\Persistence\ContentReview\Handler as HandlerInterface;
use eZ\Publish\SPI\Persistence\ContentReview\UpdateStruct;

class Handler implements HandlerInterface
{
    /** @var \eZ\Publish\Core\Persistence\Legacy\ContentReview\Gateway */
    private $gateway;

    /** @var \eZ\Publish\Core\Persistence\Legacy\ContentReview\Mapper */
    private $mapper;

    /**
     * Handler constructor.
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\ContentReview\Gateway $gateway
     * @param \eZ\Publish\Core\Persistence\Legacy\ContentReview\Mapper $mapper
     */
    public function __construct(Gateway $gateway, Mapper $mapper)
    {
        $this->gateway = $gateway;
        $this->mapper = $mapper;
    }

    /**
     * {@inheritdoc}
     */
    public function create(CreateStruct $createStruct): ContentReview
    {
        $review = $this->mapper->createReviewFromCreateStruct($createStruct);
        $review->id = $this->gateway->createReview($review);

        foreach ($createStruct->comments as $commentCreateStruct) {
            $comment = $this->mapper->createCommentFromCreateStruct($commentCreateStruct);
            $comment->reviewId = $review->id;
            $comment->id = $this->gateway->createComment($comment);

            $review->comments[] = $comment;
        }

        return $review;
    }

    /**
     * {@inheritdoc}
     */
    public function update(UpdateStruct $updateStruct): void
    {
        $this->gateway->updateReview($updateStruct);
    }

    /**
     * {@inheritdoc}
     */
    public function createComment(CommentCreateStruct $createStruct): Comment
    {
        $comment = $this->mapper->createCommentFromCreateStruct($createStruct);
        $comment->id = $this->gateway->createComment($comment);

        return $comment;
    }

    /**
     * {@inheritdoc}
     */
    public function loadByVersionInfo(int $contentId, int $versionNo): array
    {
        $reviews = $this->mapper->extractReviewsFromRows(
            $this->gateway->loadReviews($contentId, $versionNo)
        );

        foreach ($reviews as $review) {
            $review->comments = $this->mapper->extractCommentsFromRows(
                $this->gateway->loadReviewComments($review->id)
            );
        }

        return $reviews;
    }
}
