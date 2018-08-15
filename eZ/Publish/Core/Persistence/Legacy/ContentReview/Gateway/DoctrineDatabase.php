<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Legacy\ContentReview\Gateway;

use Doctrine\DBAL\Connection;
use eZ\Publish\Core\Persistence\Legacy\ContentReview\Gateway;
use eZ\Publish\Core\Persistence\Legacy\ContentReview\Gateway\Doctrine\Table\CommentTable;
use eZ\Publish\Core\Persistence\Legacy\ContentReview\Gateway\Doctrine\Table\ReviewTable;
use eZ\Publish\SPI\Persistence\ContentReview\Comment;
use eZ\Publish\SPI\Persistence\ContentReview\ContentReview;
use eZ\Publish\SPI\Persistence\ContentReview\UpdateStruct;
use PDO;

class DoctrineDatabase extends Gateway
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $connection;

    /**
     * @param \Doctrine\DBAL\Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function createReview(ContentReview $contentReview): int
    {
        $this->connection
            ->createQueryBuilder()
            ->insert(ReviewTable::NAME)
            ->values([
                ReviewTable::COLUMN_CONTENTOBJECT_ID => ':content_id',
                ReviewTable::COLUMN_VERSION => ':version',
                ReviewTable::COLUMN_USER_ID => ':user_id',
                ReviewTable::COLUMN_STATUS => ':status'
            ])
            ->setParameter(':content_id', $contentReview->contentId, PDO::PARAM_INT)
            ->setParameter(':version', $contentReview->versionNo, PDO::PARAM_INT)
            ->setParameter(':user_id', $contentReview->authorId, PDO::PARAM_INT)
            ->setParameter(':status', $contentReview->status, PDO::PARAM_INT)
            ->execute();

        return (int)$this->connection->lastInsertId();
    }

    /**
     * {@inheritdoc}
     */
    public function updateReview(UpdateStruct $updateStruct): void
    {
        $updateQuery = $this->connection->createQueryBuilder();
        $updateQuery
            ->update(ReviewTable::NAME)
            ->set(ReviewTable::COLUMN_STATUS, ':status')
            ->where(
                $updateQuery->expr()->eq(ReviewTable::COLUMN_ID, ':id')
            )
            ->setParameter('status', $updateStruct->status, PDO::PARAM_INT)
            ->setParameter('id', $updateStruct->id, PDO::PARAM_INT)
            ->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function createComment(Comment $comment): int
    {
        $this->connection
            ->createQueryBuilder()
            ->insert(CommentTable::NAME)
            ->values([
                CommentTable::COLUMN_REVIEW_ID => ':review_id',
                CommentTable::COLUMN_FIELD_DEFINITION_ID => ':field_definition_id',
                CommentTable::COLUMN_COMMENT => ':comment',
                CommentTable::COLUMN_LOCATION => ':location',
                CommentTable::COLUMN_CREATED => ':created'
            ])
            ->setParameter(':review_id', $comment->reviewId, PDO::PARAM_INT)
            ->setParameter(':field_definition_id', $comment->fieldDefinitionId, PDO::PARAM_INT)
            ->setParameter(':comment', $comment->comment, PDO::PARAM_STR)
            ->setParameter(':location', $comment->location, PDO::PARAM_STR)
            ->setParameter(':created', $comment->created, PDO::PARAM_INT)
            ->execute();

        return (int)$this->connection->lastInsertId();
    }

    /**
     * {@inheritdoc}
     */
    public function loadReviews(int $contentId, int $versionNo): array
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->select(
                ReviewTable::COLUMN_ID,
                ReviewTable::COLUMN_CONTENTOBJECT_ID,
                ReviewTable::COLUMN_VERSION,
                ReviewTable::COLUMN_USER_ID,
                ReviewTable::COLUMN_STATUS
            )
            ->from(ReviewTable::NAME)
            ->where(
                $query->expr()->andX(
                    $query->expr()->eq(ReviewTable::COLUMN_CONTENTOBJECT_ID, ':content_id'),
                    $query->expr()->eq(ReviewTable::COLUMN_VERSION, ':version')
                )
            )
            ->setParameter(':content_id', $contentId, PDO::PARAM_INT)
            ->setParameter(':version', $versionNo, PDO::PARAM_INT);

        return $query->execute()->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * {@inheritdoc}
     */
    public function loadReviewComments(int $reviewId): array
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->select(
                CommentTable::COLUMN_ID,
                CommentTable::COLUMN_REVIEW_ID,
                CommentTable::COLUMN_FIELD_DEFINITION_ID,
                CommentTable::COLUMN_COMMENT,
                CommentTable::COLUMN_LOCATION,
                CommentTable::COLUMN_CREATED
            )
            ->from(CommentTable::NAME)
            ->where(
                $query->expr()->eq(CommentTable::COLUMN_REVIEW_ID, ':review_id')
            )
            ->orderBy(CommentTable::COLUMN_CREATED, 'ASC')
            ->setParameter(':review_id', $reviewId, PDO::PARAM_INT);

        return $query->execute()->fetchAll(PDO::FETCH_ASSOC);
    }
}
