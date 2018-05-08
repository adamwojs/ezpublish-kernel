<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Cache;

use eZ\Publish\SPI\Persistence\Bookmark\Bookmark;
use eZ\Publish\SPI\Persistence\Bookmark\CreateStruct;
use eZ\Publish\SPI\Persistence\Bookmark\Handler as BookmarkHandlerInterface;

/**
 * @see \eZ\Publish\SPI\Persistence\Content\Handler
 */
class BookmarkHandler extends AbstractHandler implements BookmarkHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function create(CreateStruct $createStruct)
    {
        return $this->persistenceHandler->bookmarkHandler()->create($createStruct);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Bookmark $bookmark)
    {
        $this->persistenceHandler->bookmarkHandler()->delete($bookmark);
    }

    /**
     * {@inheritdoc}
     */
    public function loadById($id)
    {
        return $this->persistenceHandler->bookmarkHandler()->loadById($id);
    }

    /**
     * {@inheritdoc}
     */
    public function loadByUserIdAndLocationId($userId, $locationId)
    {
        return $this->persistenceHandler->bookmarkHandler()->loadByUserIdAndLocationId($userId, $locationId);
    }

    /**
     * @param int $userId
     * @return int
     */
    public function getUserBookmarksCount($userId)
    {
        return $this->persistenceHandler->bookmarkHandler()->getUserBookmarksCount($userId);
    }

    /**
     * @param int $userId
     * @param int $offset
     * @param int $limit
     * @return \eZ\Publish\SPI\Persistence\Bookmark\Bookmark[]
     */
    public function getUserBookmarks($userId, $offset, $limit)
    {
        return $this->persistenceHandler->bookmarkHandler()->getUserBookmarks($userId, $offset, $limit);
    }
}
