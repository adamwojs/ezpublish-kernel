<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Values\ContentReview;

use \eZ\Publish\API\Repository\Values\ValueObject;

abstract class ContentReview extends ValueObject
{
    public const STATUS_PENDING = 1;
    public const STATUS_COMMENT = 2;
    public const STATUS_APPROVED = 3;
    public const STATUS_REJECTED = 4;

    /**
     * ID of the review.
     *
     * @var int
     */
    protected $id;

    /**
     * ID of the user which is author of the comment.
     *
     * @var int
     */
    protected $authorId;

    /**
     * Content review status.
     *
     * @var int
     */
    protected $status;

    /**
     * Returns the VersionInfo for this version.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\VersionInfo
     */
    abstract public function getVersionInfo();

    /**
     * Get review comments related to specified field.
     *
     * @param string $fieldDefIdentifier
     * @param string $languageCode
     *
     * @return \eZ\Publish\API\Repository\Values\ContentReview\Comment[]
     */
    abstract public function getFieldComments(string $fieldDefIdentifier, string $languageCode = null): array;
}
