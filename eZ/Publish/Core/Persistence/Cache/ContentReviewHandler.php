<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Cache;

use eZ\Publish\SPI\Persistence\ContentReview\Comment;
use eZ\Publish\SPI\Persistence\ContentReview\CommentCreateStruct;
use eZ\Publish\SPI\Persistence\ContentReview\ContentReview;
use eZ\Publish\SPI\Persistence\ContentReview\CreateStruct;
use eZ\Publish\SPI\Persistence\ContentReview\Handler as ContentReviewHandlerInterface;
use eZ\Publish\SPI\Persistence\ContentReview\UpdateStruct;

class ContentReviewHandler extends AbstractHandler implements ContentReviewHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function create(CreateStruct $createStruct): ContentReview
    {
        $this->logger->logCall(__METHOD__, [
            'struct' => $createStruct
        ]);

        return $this->persistenceHandler->contentReviewHandler()->create($createStruct);
    }

    /**
     * {@inheritdoc}
     */
    public function update(UpdateStruct $updateStruct): void
    {
        $this->logger->logCall(__METHOD__, [
            'struct' => $updateStruct
        ]);

        $this->persistenceHandler->contentReviewHandler()->update($updateStruct);
    }

    /**
     * {@inheritdoc}
     */
    public function createComment(CommentCreateStruct $createStruct): Comment
    {
        $this->logger->logCall(__METHOD__, [
            'struct' => $createStruct
        ]);

        return $this->persistenceHandler->contentReviewHandler()->createComment($createStruct);
    }

    /**
     * {@inheritdoc}
     */
    public function loadByVersionInfo(int $contentId, int $versionNo): array
    {
        $this->logger->logCall(__METHOD__, [
            'contentId' => $contentId,
            'versionNo' => $versionNo
        ]);

        return $this->persistenceHandler->contentReviewHandler()->loadByVersionInfo($contentId, $versionNo);
    }
}
