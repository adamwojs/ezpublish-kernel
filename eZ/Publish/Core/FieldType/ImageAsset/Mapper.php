<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\FieldType\ImageAsset;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException;
use eZ\Publish\API\Repository\Exceptions\ContentValidationException;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\FieldType\Image\Value as ImageValue;

class Mapper
{
    /** @var \eZ\Publish\API\Repository\ContentService */
    private $contentService;
    /** @var \eZ\Publish\API\Repository\LocationService */
    private $locationService;
    /** @var \eZ\Publish\API\Repository\ContentTypeService */
    private $contentTypeService;

    /** @var string */
    private $contentTypeIdentifier;
    /** @var string */
    private $fileFieldIdentifier;
    /** @var string */
    private $nameFieldIdentifier;
    /** @var string */
    private $parentLocationId;

    /**
     * Mapper constructor.
     *
     * @param \eZ\Publish\API\Repository\ContentService $contentService
     * @param \eZ\Publish\API\Repository\LocationService $locationService
     * @param \eZ\Publish\API\Repository\ContentTypeService $contentTypeService
     */
    public function __construct(
        ContentService $contentService,
        LocationService $locationService,
        ContentTypeService $contentTypeService)
    {
        $this->contentService = $contentService;
        $this->locationService = $locationService;
        $this->contentTypeService = $contentTypeService;
    }

    /**
     * @param string $name
     * @param string $file
     * @param string $languageCode
     * @param string $alternativeText
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    public function createImageAsset(string $name, ImageValue $image, string $languageCode): Content
    {
        try {
            $contentType = $this->contentTypeService->loadContentTypeByIdentifier($this->contentTypeIdentifier);

            $contentCreateStruct = $this->contentService->newContentCreateStruct($contentType, $languageCode);
            $contentCreateStruct->setField($this->nameFieldIdentifier, $name);
            $contentCreateStruct->setField($this->fileFieldIdentifier, $image);

            $contentDraft = $this->contentService->createContent($contentCreateStruct, [
                $this->locationService->newLocationCreateStruct($this->parentLocationId)
            ]);

            return $this->contentService->publishVersion($contentDraft->versionInfo);
        } catch (NotFoundException $e) {
        } catch (ContentFieldValidationException $e) {
        } catch (ContentValidationException $e) {
        }
    }

    public function loadImageAssetById($contentId, $languageCode): ImageValue
    {
        try {
            return $this->loadImageAsset(
                $this->contentService->loadContent($contentId . [$languageCode])
            );
        } catch (NotFoundException $e) {
            throw $e;
        }
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     * @return \eZ\Publish\Core\FieldType\Image\Value
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException
     */
    public function loadImageAsset(Content $content): ImageValue
    {
        $contentType = $this->contentTypeService->loadContentType($content->contentInfo->contentTypeId);
        if ($contentType->identifier !== $this->contentTypeIdentifier) {
            throw new InvalidArgumentException("Content {$content->id} is not a image asset.");
        }

        return $content->getFieldValue($this->fileFieldIdentifier);
    }


    public function setContentTypeIdentifier(string $contentTypeIdentifier): void
    {
        $this->contentTypeIdentifier = $contentTypeIdentifier;
    }

    public function setFileFieldIdentifier(string $fileFieldIdentifier): void
    {
        $this->fileFieldIdentifier = $fileFieldIdentifier;
    }

    public function setNameFieldIdentifier(string $nameFieldIdentifier): void
    {
        $this->nameFieldIdentifier = $nameFieldIdentifier;
    }

    public function setParentLocationId(string $parentLocationId): void
    {
        $this->parentLocationId = $parentLocationId;
    }
}
