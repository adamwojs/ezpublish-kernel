<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Legacy\ContentReview;

use eZ\Publish\SPI\Persistence\ContentReview\Comment;
use eZ\Publish\SPI\Persistence\ContentReview\CommentCreateStruct as CommentCreateStruct;
use eZ\Publish\SPI\Persistence\ContentReview\ContentReview;
use eZ\Publish\SPI\Persistence\ContentReview\CreateStruct as ReviewCreateStruct;

class Mapper
{
    public function createReviewFromCreateStruct(ReviewCreateStruct $createStruct): ContentReview
    {
        $review = new ContentReview();
        $review->contentId = $createStruct->contentId;
        $review->authorId = $createStruct->authorId;
        $review->versionNo = $createStruct->versionNo;
        $review->status = $createStruct->status;

        return $review;
    }

    public function createCommentFromCreateStruct(CommentCreateStruct $createStruct): Comment
    {
        $comment = new Comment();
        $comment->reviewId = $createStruct->reviewId;
        $comment->fieldDefinitionId = $createStruct->fieldDefinitionId;
        $comment->comment = $createStruct->comment;
        $comment->location = $createStruct->location;
        $comment->created = $createStruct->created;

        return $comment;
    }

    /**
     * @param array $rows
     * @return \eZ\Publish\SPI\Persistence\ContentReview\ContentReview[]
     */
    public function extractReviewsFromRows(array $rows): array
    {
        $reviews = [];
        foreach ($rows as $row) {
            $reviews[] = $this->extractReviewFromRow($row);
        }

        return $reviews;
    }

    private function extractReviewFromRow(array $row): ContentReview
    {
        $review = new ContentReview();
        $review->id = (int)$row['id'];
        $review->contentId = (int)$row['contentobject_id'];
        $review->versionNo = (int)$row['version'];
        $review->authorId = (int)$row['user_id'];
        $review->status = (int)$row['status'];

        return $review;
    }

    /**
     * @param array $rows
     * @return \eZ\Publish\SPI\Persistence\ContentReview\Comment[]
     */
    public function extractCommentsFromRows(array $rows): array
    {
        $comments = [];
        foreach($rows as $row) {
            $comments[] = $this->extractCommentFromRow($row);
        }

        return $comments;
    }

    private function extractCommentFromRow($row): Comment
    {
        $comment = new Comment();
        $comment->id = (int)$row['id'];
        $comment->reviewId = (int)$row['contentobject_review_id'];
        $comment->fieldDefinitionId = (int)$row['contentclass_attribute_id'];
        $comment->comment = $row['comment'];
        $comment->location = $row['location'];
        $comment->created = (int)$row['created'];

        return $comment;
    }
}
