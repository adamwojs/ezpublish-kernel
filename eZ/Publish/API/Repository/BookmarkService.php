<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository;

use eZ\Publish\API\Repository\Values\Bookmark\BookmarkList;
use eZ\Publish\API\Repository\Values\Content\Location;

/**
 * Bookmark Service.
 */
interface BookmarkService
{
    /**
     * Add location to bookmarks.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     * @return void
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function createBookmark(Location $location);

    /**
     * Deletes location from bookmarks.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     * @return void
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function deleteBookmark(Location $location);

    /**
     * Lists bookmarked locations.
     *
     * @param int $offset
     * @param int $limit
     * @return \eZ\Publish\API\Repository\Values\Bookmark\BookmarkList
     */
    public function loadBookmarks($offset = 0, $limit = -1): BookmarkList;

    /**
     * Returns true if location is bookmarked.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     * @return boolean
     */
    public function isBookmarked(Location $location): bool;
}
