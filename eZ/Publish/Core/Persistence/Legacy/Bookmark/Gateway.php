<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Bookmark;

use eZ\Publish\SPI\Persistence\Bookmark\Bookmark;

abstract class Gateway
{
    abstract public function insertBookmark(Bookmark $bookmark);

    abstract public function deleteBookmark($id);

    abstract public function loadBookmarkDataById($id);

    abstract public function loadBookmarkDataByUserIdAndLocationId($userId, $locationId);

    abstract public function getUserBookmarks($userId, $offset, $limit);
}
