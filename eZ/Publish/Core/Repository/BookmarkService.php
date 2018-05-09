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
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\SPI\Persistence\Bookmark\Bookmark;
use eZ\Publish\SPI\Persistence\Bookmark\CreateStruct;
use eZ\Publish\SPI\Persistence\Bookmark\Handler as BookmarkHandler;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;

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
    public function createBookmark(Location $location): void
    {
        $loadedLocation = $this->repository->getLocationService()->loadLocation($location->id);

        if ($this->isBookmarked($loadedLocation)) {
            throw new InvalidArgumentException('$location', 'location is already bookmarked.');
        }

        $createStruct = new CreateStruct();
        $createStruct->name = $loadedLocation->contentInfo->name;
        $createStruct->locationId = $loadedLocation->id;
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
    public function deleteBookmark(Location $location): void
    {
        $loadedLocation = $this->repository->getLocationService()->loadLocation($location->id);

        try {
            $bookmark = $this->bookmarkHandler->loadByUserIdAndLocationId($this->getCurrentUserId(), $loadedLocation->id);
        } catch (NotFoundException $e) {
            throw new InvalidArgumentException('$location', 'location is not bookmarked.');
        }

        $this->repository->beginTransaction();
        try {
            $this->bookmarkHandler->delete($bookmark->id);
            $this->repository->commit();
        } catch (Exception $ex) {
            $this->repository->rollback();
            throw $ex;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function loadBookmarks(int $offset = 0, int $limit = -1): BookmarkList
    {
        $bookmarksIds = array_map(function (Bookmark $bookmark) {
            return $bookmark->locationId;
        }, $this->bookmarkHandler->loadUserBookmarks($this->getCurrentUserId(), $offset, $limit));

        $query = new LocationQuery([
            'filter' => new Criterion\LocationId($bookmarksIds),
            'offset' => $offset >= 0 ? (int)$offset : 0,
            'limit' => $limit >= 0 ? (int)$limit : null
        ]);

        $results = $this->repository->getSearchService()->findLocations($query);

        $list = new BookmarkList();
        $list->totalCount = $results->totalCount;
        foreach ($results->searchHits as $searchHit) {
            $list->items[] = $searchHit->valueObject;
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
