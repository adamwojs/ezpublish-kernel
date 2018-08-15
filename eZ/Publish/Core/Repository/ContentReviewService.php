<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Repository;

use DateTime;
use Exception;
use eZ\Publish\API\Repository\ContentReviewService as ContentReviewServiceInterface;
use eZ\Publish\API\Repository\Values\Content\VersionInfo;
use eZ\Publish\API\Repository\Values\ContentReview\Comment;
use eZ\Publish\API\Repository\Values\ContentReview\CommentCreateStruct;
use eZ\Publish\API\Repository\Values\ContentReview\ContentReview as APIContentReview;
use eZ\Publish\API\Repository\Values\ContentReview\CreateStruct;
use eZ\Publish\API\Repository\Values\ContentReview\UpdateStruct;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\API\Repository\Values\User\User;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\Repository\Values\ContentReview\ContentReview;
use eZ\Publish\SPI\Persistence\ContentReview\CommentCreateStruct as SPICommentCreateStruct;
use eZ\Publish\SPI\Persistence\ContentReview\ContentReview as SPIContentReview;
use eZ\Publish\SPI\Persistence\ContentReview\CreateStruct as SPICreateStruct;
use eZ\Publish\SPI\Persistence\ContentReview\UpdateStruct as SPIUpdateStruct;
use eZ\Publish\SPI\Persistence\ContentReview\Handler as ContentReviewHandler;

class ContentReviewService implements ContentReviewServiceInterface
{
    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    protected $repository;

    /**
     * @var \eZ\Publish\SPI\Persistence\ContentReview\Handler
     */
    protected $persistenceHandler;

    /**
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param \eZ\Publish\SPI\Persistence\ContentReview\Handler $persistenceHandler
     */
    public function __construct(Repository $repository, ContentReviewHandler $persistenceHandler)
    {
        $this->repository = $repository;
        $this->persistenceHandler = $persistenceHandler;
    }

    /**
     * {@inheritdoc}
     */
    public function createReview(VersionInfo $versionInfo, CreateStruct $createStruct): APIContentReview
    {
        $contentType = $this->repository->getContentTypeService()->loadContentType(
            $versionInfo->getContentInfo()->contentTypeId
        );

        $spiCreateStruct = new SPICreateStruct();
        $spiCreateStruct->authorId = $this->getCurrentUserId();
        $spiCreateStruct->contentId = $versionInfo->contentInfo->id;
        $spiCreateStruct->versionNo = $versionInfo->versionNo;
        $spiCreateStruct->status = ContentReview::STATUS_PENDING;
        $spiCreateStruct->comments = array_map(function (CommentCreateStruct $commentCreateStruct) use ($contentType) {
            $fieldDefinition = $contentType->getFieldDefinition(
                $commentCreateStruct->fieldDefIdentifier
            );

            $spiCommentCreateStruct = new SPICommentCreateStruct();
            $spiCommentCreateStruct->fieldDefinitionId = $fieldDefinition->id;
            $spiCommentCreateStruct->comment = $commentCreateStruct->comment;
            $spiCommentCreateStruct->location = $commentCreateStruct->location;
            $spiCommentCreateStruct->created = time();

            return $spiCommentCreateStruct;
        }, $createStruct->comments);

        $this->repository->beginTransaction();
        try {
            $spiContentReview = $this->persistenceHandler->create(
                $spiCreateStruct
            );
            $this->repository->commit();
        } catch (Exception $ex) {
            $this->repository->rollback();
            throw $ex;
        }

        return $this->buildDomainObject($versionInfo, $contentType, $spiContentReview);
    }

    /**
     * {@inheritdoc}
     */
    public function updateReview(APIContentReview $review, UpdateStruct $updateStruct): void
    {
        $spiUpdateStruct = new SPIUpdateStruct();
        $spiUpdateStruct->id = $review->id;
        $spiUpdateStruct->status = $updateStruct->status;

        $this->repository->beginTransaction();
        try {
            $this->persistenceHandler->update($spiUpdateStruct);
            $this->repository->commit();
        } catch (Exception $ex) {
            $this->repository->rollback();
            throw $ex;
        }
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\VersionInfo $versionInfo
     * @param \eZ\Publish\API\Repository\Values\User\User|null $user
     *
     * @return \eZ\Publish\API\Repository\Values\ContentReview\ContentReview
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\NotFoundException
     */
    public function loadReviewByUser(VersionInfo $versionInfo, User $user = null): APIContentReview
    {
        $authorId = $this->getCurrentUserId();
        if ($user !== null) {
            $authorId = $user->getUserId();
        }

        // TODO: Move to dedicated persistance handler method

        $spiReviews = $this->persistenceHandler->loadByVersionInfo(
            $versionInfo->contentInfo->id,
            $versionInfo->versionNo
        );

        foreach ($spiReviews as $spiReview) {
            if ($spiReview->authorId == $authorId) {
                $contentType = $this->repository->getContentTypeService()->loadContentType(
                    $versionInfo->contentInfo->contentTypeId
                );

                return $this->buildDomainObject($versionInfo, $contentType, $spiReview);
            }
        }

        throw new NotFoundException("ContentReview", $authorId);
    }

    /**
     * {@inheritdoc}
     */
    public function loadReviews(VersionInfo $versionInfo): array
    {
        $contentType = $this->repository->getContentTypeService()->loadContentType(
            $versionInfo->contentInfo->contentTypeId
        );

        $spiReviews = $this->persistenceHandler->loadByVersionInfo(
            $versionInfo->contentInfo->id,
            $versionInfo->versionNo
        );

        return array_map(function (SPIContentReview $data) use ($versionInfo, $contentType) {
            return $this->buildDomainObject($versionInfo, $contentType, $data);
        }, $spiReviews);
    }

    /**
     * {@inheritdoc}
     */
    public function createComment(APIContentReview $review, CommentCreateStruct $createStruct): void
    {
        $contentType = $this->repository->getContentTypeService()->loadContentType(
            $review->getVersionInfo()->getContentInfo()->contentTypeId
        );

        $fieldDefinition = $contentType->getFieldDefinition(
            $createStruct->fieldDefIdentifier
        );

        $spiCreateStruct = new SPICommentCreateStruct();
        $spiCreateStruct->fieldDefinitionId = $fieldDefinition->id;
        $spiCreateStruct->comment = $createStruct->comment;
        $spiCreateStruct->reviewId = $review->id;
        $spiCreateStruct->location = $createStruct->location;
        $spiCreateStruct->created = time();

        $this->repository->beginTransaction();
        try {
            $this->persistenceHandler->createComment($spiCreateStruct);

            if ($review->status === ContentReview::STATUS_PENDING) {
                $this->persistenceHandler->update(new SPIUpdateStruct([
                    'id' => $review->id,
                    'status' => ContentReview::STATUS_COMMENT
                ]));
            }

            $this->repository->commit();
        } catch (Exception $ex) {
            $this->repository->rollback();
            throw $ex;
        }
    }

    protected function buildDomainObject(
        VersionInfo $versionInfo,
        ContentType $contentType,
        SPIContentReview $data): ContentReview
    {
        $comments = [];
        foreach ($data->comments as $spiComment) {
            $fieldDefinition = null;
            foreach ($contentType->getFieldDefinitions() as $definition) {
                if ($definition->id === $spiComment->fieldDefinitionId) {
                    $fieldDefinition = $definition;
                    break;
                }
            }

            $comments[] = new Comment([
                'id' => $spiComment->id,
                'fieldDefIdentifier' => $fieldDefinition->identifier,
                'comment' => $spiComment->comment,
                'location' => $spiComment->location,
                'created' => new DateTime("@{$spiComment->created}")
            ]);
        }

        return new ContentReview($data->id, $versionInfo, $data->authorId, $comments, $data->status);
    }

    private function getCurrentUserId(): int
    {
        return $this->repository
            ->getPermissionResolver()
            ->getCurrentUserReference()
            ->getUserId();
    }
}
