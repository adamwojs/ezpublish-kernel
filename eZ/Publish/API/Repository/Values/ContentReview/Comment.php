<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Values\ContentReview;

use \eZ\Publish\API\Repository\Values\ValueObject;

class Comment extends ValueObject
{
    /**
     * The commend ID.
     *
     * @var int
     */
    protected $id;

    /**
     * The field definition identifier
     *
     * @var string
     */
    protected $fieldDefIdentifier;

    /**
     * The review comment
     *
     * @var string
     */
    protected $comment;

    /**
     * @var mixed
     */
    protected $location;

    /**
     * Creation date.
     *
     * @var \DateTimeInterface
     */
    protected $created;
}
