<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\Repository;

use Exception;
use eZ\Publish\API\Repository\BookmarkService as BookmarkServiceInterface;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\Values\Bookmark\BookmarkList;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion as ContentCriterion;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\SPI\Persistence\Bookmark\CreateStruct;
use eZ\Publish\SPI\Persistence\Bookmark\Handler as BookmarkHandler;

class BookmarkService implements BookmarkServiceInterface
{
    /**
     * @var \eZ\Publish\Core\Repository\Repository
     */
    protected $repository;

    /**
     * @var \eZ\Publish\SPI\Persistence\Bookmark\Handler
     */
    protected $bookmarkHandler;

    /**
     * BookmarkService constructor.
     *
     * @param Repository $repository
     * @param \eZ\Publish\SPI\Persistence\Bookmark\Handler $bookmarkHandler
     */
    public function __construct(Repository $repository, BookmarkHandler $bookmarkHandler)
    {
        $this->repository = $repository;
        $this->bookmarkHandler = $bookmarkHandler;
    }

    /**
     * {@inheritdoc}
     */
    public function createBookmark(Location $location)
    {
        if ($this->isBookmarked($location)) {
            throw new InvalidArgumentException('$location', 'location is already in bookmarks.');
        }

        $createStruct = new CreateStruct();
        $createStruct->name = $location->contentInfo->name;
        $createStruct->locationId = $location->id;
        $createStruct->userId = $this->getCurrentUserId();

        $this->repository->beginTransaction();
        try {
            $this->bookmarkHandler->create($createStruct);
            $this->repository->commit();
        } catch (Exception $ex) {
            $this->repository->rollback();
            throw $ex;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deleteBookmark(Location $location)
    {
        $bookmark = $this->bookmarkHandler->loadByUserIdAndLocationId($this->getCurrentUserId(), $location->id);

        $this->repository->beginTransaction();
        try {
            $this->bookmarkHandler->delete($bookmark);
            $this->repository->commit();
        } catch (Exception $ex) {
            $this->repository->rollback();
            throw $ex;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function loadBookmarks($offset = 0, $limit = -1): BookmarkList
    {
        $userId = $this->getCurrentUserId();

        $list = new BookmarkList();
        $list->totalCount = $this->bookmarkHandler->getUserBookmarksCount($userId);
        if ($list->totalCount > 0) {
            $list->items = $this->bookmarkHandler->getUserBookmarks($userId, $offset, $limit);
        }

        return $list;
    }

    /**
     * {@inheritdoc}
     */
    public function isBookmarked(Location $location): bool
    {
        try {
            $this->bookmarkHandler->loadByUserIdAndLocationId($this->getCurrentUserId(), $location->id);
            return true;
        } catch (NotFoundException $e) {
            return false;
        }
    }

    private function getCurrentUserId()
    {
        return $this->repository
            ->getPermissionResolver()
            ->getCurrentUserReference()
            ->getUserId();
    }
}
