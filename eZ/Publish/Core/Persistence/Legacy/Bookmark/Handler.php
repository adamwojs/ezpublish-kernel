<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Bookmark;

use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\SPI\Persistence\Bookmark\Bookmark;
use eZ\Publish\SPI\Persistence\Bookmark\CreateStruct;
use eZ\Publish\SPI\Persistence\Bookmark\Handler as HandlerInterface;

/**
 * Storage Engine handler for bookmarks.
 */
class Handler implements HandlerInterface
{
    /** @var \eZ\Publish\Core\Persistence\Legacy\Bookmark\Gateway */
    private $gateway;

    /** @var \eZ\Publish\Core\Persistence\Legacy\Bookmark\Mapper */
    private $mapper;

    /**
     * Handler constructor.
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Bookmark\Gateway $gateway
     * @param \eZ\Publish\Core\Persistence\Legacy\Bookmark\Mapper $mapper
     */
    public function __construct(Gateway $gateway, Mapper $mapper)
    {
        $this->gateway = $gateway;
        $this->mapper = $mapper;
    }

    /**
     * {@inheritdoc}
     */
    public function create(CreateStruct $createStruct)
    {
        $bookmark = $this->mapper->createBookmarkFromCreateStruct(
            $createStruct
        );
        $bookmark->id = $this->gateway->insertBookmark($bookmark);

        return $bookmark;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Bookmark $bookmark)
    {
        $this->gateway->deleteBookmark($bookmark->id);
    }

    /**
     * {@inheritdoc}
     */
    public function loadById($id)
    {
        $bookmark = $this->mapper->extractBookmarksFromRows(
            $this->gateway->loadBookmarkDataById($id)
        );

        if (count($bookmark) < 1) {
            throw new NotFoundException('Bookmark', $id);
        }

        return reset($bookmark);
    }

    /**
     * {@inheritdoc}
     */
    public function loadByUserIdAndLocationId($userId, $locationId)
    {
        $bookmark = $this->mapper->extractBookmarksFromRows(
            $this->gateway->loadBookmarkDataByUserIdAndLocationId($userId, $locationId)
        );

        if (count($bookmark) < 1) {
            throw new NotFoundException('Bookmark', [
                'userId' => $userId,
                'locationId' => $locationId
            ]);
        }

        return reset($bookmark);
    }

    /**
     * @param int $userId
     * @return int
     */
    public function getUserBookmarksCount($userId)
    {
        returN $this->gateway->countUserBookmarks($userId);
    }

    /**
     * @param int $userId
     * @param int $offset
     * @param int $limit
     * @return \eZ\Publish\SPI\Persistence\Bookmark\Bookmark[]
     */
    public function getUserBookmarks($userId, $offset, $limit)
    {
    }
}
