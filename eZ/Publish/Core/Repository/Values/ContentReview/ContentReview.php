<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Repository\Values\ContentReview;

use eZ\Publish\API\Repository\Values\Content\VersionInfo;
use eZ\Publish\API\Repository\Values\ContentReview\Comment;
use eZ\Publish\API\Repository\Values\ContentReview\ContentReview as APIContentReview;

class ContentReview extends APIContentReview
{
    /**
     * @var \eZ\Publish\API\Repository\Values\Content\VersionInfo
     */
    protected $versionInfo;

    /**
     * @var \eZ\Publish\API\Repository\Values\ContentReview\Comment[]
     */
    protected $comments;

    /**
     * ContentReview constructor.
     *
     * @param int $id
     * @param \eZ\Publish\API\Repository\Values\Content\VersionInfo $versionInfo
     * @param int $authorId
     * @param \eZ\Publish\API\Repository\Values\ContentReview\Comment[] $comments
     * @param int $status
     */
    public function __construct(
        int $id,
        VersionInfo $versionInfo,
        int $authorId,
        array $comments,
        int $status = self::STATUS_PENDING)
    {
        parent::__construct([
            'id' => $id,
            'versionInfo' => $versionInfo,
            'authorId' => $authorId,
            'comments' => $comments,
            'status' => $status
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getVersionInfo(): VersionInfo
    {
        return $this->versionInfo;
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldComments(string $fieldDefIdentifier, string $languageCode = null): array
    {
        return array_filter($this->comments, function (Comment $comment) use ($fieldDefIdentifier) {
            return $comment->fieldDefIdentifier === $fieldDefIdentifier;
        });
    }
}
