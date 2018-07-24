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
use eZ\Publish\API\Repository\Values\Content\Field;
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
    /** @var int */
    private $contentTypeId;
    /** @var string */
    private $fileFieldIdentifier;
    /** @var string */
    private $nameFieldIdentifier;
    /** @var string */
    private $parentLocationId;

    /**
     * @param \eZ\Publish\API\Repository\ContentService $contentService
     * @param \eZ\Publish\API\Repository\LocationService $locationService
     * @param \eZ\Publish\API\Repository\ContentTypeService $contentTypeService
     */
    public function __construct(
        ContentService $contentService,
        LocationService $locationService,
        ContentTypeService $contentTypeService,
        array $config = [])
    {
        $this->contentService = $contentService;
        $this->locationService = $locationService;
        $this->contentTypeService = $contentTypeService;
    }

    /**
     * @param string $name
     * @param \eZ\Publish\Core\FieldType\Image\Value $image
     * @param string $languageCode
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    public function createAsset(string $name, ImageValue $image, string $languageCode): Content
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

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Field
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException
     */
    public function getAssetField(Content $content): Field
    {
        if (!$this->isValidDestinationContent($content)) {
            throw new InvalidArgumentException("contentId", "Content {$content->id} is not a image asset!");
        }

        return $content->getField($this->fileFieldIdentifier);
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     *
     * @return \eZ\Publish\Core\FieldType\Image\Value
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException
     */
    public function getAssetValue(Content $content): ImageValue
    {
        if (!$this->isValidDestinationContent($content)) {
            throw new InvalidArgumentException("contentId", "Content {$content->id} is not a image asset!");
        }

        return $content->getFieldValue($this->fileFieldIdentifier);
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     * @return bool
     */
    public function isValidDestinationContent(Content $content): bool
    {
        return $content->contentInfo->contentTypeId === $this->getContentTypeId();
    }

    public function setContentTypeIdentifier(string $contentTypeIdentifier): void
    {
        $this->contentTypeIdentifier = $contentTypeIdentifier;
        $this->contentTypeId = null;
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

    private function getContentTypeId(): ?int
    {
        if ($this->contentTypeId === null) {
            $this->contentTypeId = $this
                ->contentTypeService
                ->loadContentTypeByIdentifier($this->contentTypeIdentifier)
                ->id;
        }

        return $this->contentTypeId;
    }
}
