<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\REST\Server\Values;

use eZ\Publish\Core\REST\Common\Value as RestValue;
use eZ\Publish\API\Repository\Values\Content\Location;

class LocationIsBookmarked extends RestValue
{
    /** @var \eZ\Publish\API\Repository\Values\Content\Location */
    public $location;

    /** @var bool */
    public $isBookmarked;

    /**
     * LocationIsBookmarked constructor.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     * @param bool $isBookmarked
     */
    public function __construct(Location $location, bool $isBookmarked)
    {
        $this->location = $location;
        $this->isBookmarked = $isBookmarked;
    }
}
