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
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException When location is already bookmarked
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function createBookmark(Location $location): void;

    /**
     * Removes given location from bookmarks.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function deleteBookmark(Location $location): void;

    /**
     * List bookmarked locations.
     *
     * @param int $offset the start offset for paging
     * @param int $limit the number of bookmarked locations returned
     * @return \eZ\Publish\API\Repository\Values\Bookmark\BookmarkList
     */
    public function loadBookmarks(int $offset = 0, int $limit = -1): BookmarkList;

    /**
     * Returns true if location is bookmarked.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     * @return bool
     */
    public function isBookmarked(Location $location): bool;
}
