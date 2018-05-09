<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\SignalSlot\Signal\BookmarkService;

use eZ\Publish\Core\SignalSlot\Signal;

class DeleteBookmarkSignal extends Signal
{
    /**
     * Location ID.
     *
     * @var int
     */
    public $locationId;
}
