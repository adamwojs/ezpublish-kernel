<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Values\ContentReview;

use \eZ\Publish\API\Repository\Values\ValueObject;

class CreateStruct extends ValueObject
{
    /**
     * @var \eZ\Publish\API\Repository\Values\ContentReview\CommentCreateStruct[]
     */
    public $comments = [];
}
