<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\SignalSlot;

use eZ\Publish\API\Repository\BookmarkService as BookmarkServiceInterface;
use eZ\Publish\API\Repository\Values\Bookmark\BookmarkList;
use eZ\Publish\API\Repository\Values\Content\Location;

class BookmarkService implements BookmarkServiceInterface
{
    /**
     * Aggregated service.
     *
     * @var \eZ\Publish\API\Repository\BookmarkService
     */
    protected $service;

    /**
     * SignalDispatcher.
     *
     * @var \eZ\Publish\Core\SignalSlot\SignalDispatcher
     */
    protected $signalDispatcher;

    /**
     * BookmarkService constructor.
     *
     * @param \eZ\Publish\API\Repository\BookmarkService $service
     * @param \eZ\Publish\Core\SignalSlot\SignalDispatcher $signalDispatcher
     */
    public function __construct(BookmarkServiceInterface $service, SignalDispatcher $signalDispatcher)
    {
        $this->service = $service;
        $this->signalDispatcher = $signalDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function createBookmark(Location $location)
    {
        $this->service->createBookmark($location);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteBookmark(Location $location)
    {
        $this->service->deleteBookmark($location);
    }

    /**
     * {@inheritdoc}
     */
    public function loadBookmarks($offset = 0, $limit = -1): BookmarkList
    {
        return $this->service->loadBookmarks($offset, $limit);
    }

    /**
     * {@inheritdoc}
     */
    public function isBookmarked(Location $location): bool
    {
        return $this->service->isBookmarked($location);
    }
}
