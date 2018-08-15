<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Values\ContentReview;

use eZ\Publish\API\Repository\Values\ValueObject;

class CommentCreateStruct extends ValueObject
{
    /**
     * @var string The field definition identifier
     */
    public $fieldDefIdentifier;

    /**
     * @var string The review comment
     */
    public $comment;

    /**
     * @var string
     */
    public $location;

    /**
     * @var int
     */
    public $status = ContentReview::STATUS_PENDING;
}
