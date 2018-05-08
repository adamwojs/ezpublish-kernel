<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\SPI\Persistence\Bookmark;

interface Handler
{
    /**
     * @param \eZ\Publish\SPI\Persistence\Bookmark\CreateStruct $createStruct
     * @return \eZ\Publish\SPI\Persistence\Bookmark\Bookmark
     */
    public function create(CreateStruct $createStruct);

    /**
     * @param \eZ\Publish\SPI\Persistence\Bookmark\Bookmark $bookmark
     * @return void
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function delete(Bookmark $bookmark);

    /**
     * @param int $userId
     * @return int
     */
    public function getUserBookmarksCount($userId);

    /**
     * @param int $userId
     * @param int $offset
     * @param int $limit
     * @return \eZ\Publish\SPI\Persistence\Bookmark\Bookmark[]
     */
    public function getUserBookmarks($userId, $offset, $limit);

    /**
     * @param int $id
     * @return \eZ\Publish\SPI\Persistence\Bookmark\Bookmark
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function loadById($id);

    /**
     * @param int $userId
     * @param int $locationId
     * @return \eZ\Publish\SPI\Persistence\Bookmark\Bookmark
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function loadByUserIdAndLocationId($userId, $locationId);

}
