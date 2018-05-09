<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository;

use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Bookmark\BookmarkList;

/**
 * Bookmark Service.
 *
 * Service to handle bookmarking of Content item Locations. It works in the context of a current User (obtained from
 * the PermissionResolver).
 */
interface BookmarkService
{
    /**
     * Add location to bookmarks.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException When location is already bookmarked
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user user is not allowed to create bookmark
     */
    public function createBookmark(Location $location): void;

    /**
     * Remove given location from bookmarks.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException When location is not bookmarked
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException f the current user user is not allowed to delete bookmark
     */
    public function deleteBookmark(Location $location): void;

    /**
     * List bookmarked locations.
     *
     * @param int $offset the start offset for paging
     * @param int $limit the number of bookmarked locations returned
     *
     * @return \eZ\Publish\API\Repository\Values\Bookmark\BookmarkList
     */
    public function loadBookmarks(int $offset = 0, int $limit = -1): BookmarkList;

    /**
     * Return true if location is bookmarked.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     *
     * @return bool
     */
    public function isBookmarked(Location $location): bool;
}
