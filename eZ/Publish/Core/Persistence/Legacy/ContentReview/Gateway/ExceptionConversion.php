<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Legacy\ContentReview\Gateway;

use Doctrine\DBAL\DBALException;
use eZ\Publish\Core\Persistence\Legacy\ContentReview\Gateway;
use eZ\Publish\SPI\Persistence\ContentReview\Comment;
use eZ\Publish\SPI\Persistence\ContentReview\ContentReview;
use eZ\Publish\SPI\Persistence\ContentReview\UpdateStruct;
use PDOException;
use RuntimeException;

class ExceptionConversion extends Gateway
{
    /**
     * The wrapped gateway.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\ContentReview\Gateway
     */
    protected $innerGateway;

    /**
     * ExceptionConversion constructor.
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\ContentReview\Gateway $innerGateway
     */
    public function __construct(Gateway $innerGateway)
    {
        $this->innerGateway = $innerGateway;
    }

    /**
     * {@inheritdoc}
     */
    public function createReview(ContentReview $contentReview): int
    {
        try {
            return $this->innerGateway->createReview($contentReview);
        } catch (DBALException | PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function updateReview(UpdateStruct $updateStruct): void
    {
        try {
            $this->innerGateway->updateReview($updateStruct);
        } catch (DBALException | PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function createComment(Comment $comment): int
    {
        try {
            return $this->innerGateway->createComment($comment);
        } catch (DBALException | PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function loadReviews(int $contentId, int $versionNo): array
    {
        try {
            return $this->innerGateway->loadReviews($contentId, $versionNo);
        } catch (DBALException | PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function loadReviewComments(int $reviewId): array
    {
        try {
            return $this->innerGateway->loadReviewComments($reviewId);
        } catch (DBALException | PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }
}
