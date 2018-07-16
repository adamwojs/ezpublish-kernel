<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\FieldType\ImageAsset;

use eZ\Publish\Core\FieldType\Image\Value as BaseValue;

class Value extends BaseValue
{
    /**
     * Related content id's.
     *
     * @var mixed|null
     */
    public $destinationContentId;

    /**
     * @param mixed|null $destinationContentId
     * @param array $imageData
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function __construct($destinationContentId = null, array $imageData = [])
    {
        parent::__construct($imageData);

        $this->destinationContentId = $destinationContentId;
    }
}
