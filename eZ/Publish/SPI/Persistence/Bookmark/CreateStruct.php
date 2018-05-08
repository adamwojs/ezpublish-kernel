<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\SPI\Persistence\Bookmark;

use eZ\Publish\SPI\Persistence\ValueObject;

class CreateStruct extends ValueObject
{
    /**
     * @var string
     */
    public $name;

    /**
     * ID of the bookmarked Location.
     *
     * @var mixed
     */
    public $locationId;

    /**
     * ID of bookmark owner.
     *
     * @var int
     */
    public $userId;

}
