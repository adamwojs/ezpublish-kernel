<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\SPI\Persistence\ContentReview;

use eZ\Publish\SPI\Persistence\ValueObject;

class ContentReview extends ValueObject
{
    public $id;

    public $contentId;

    public $versionNo;

    public $authorId;

    public $comments = [];

    public $status;

}
