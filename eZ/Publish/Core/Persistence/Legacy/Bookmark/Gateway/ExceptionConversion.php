<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Bookmark\Gateway;

use Doctrine\DBAL\DBALException;
use eZ\Publish\Core\Persistence\Legacy\Bookmark\Gateway;
use eZ\Publish\SPI\Persistence\Bookmark\Bookmark;
use PDOException;
use RuntimeException;

class ExceptionConversion extends Gateway
{
    /**
     * The wrapped gateway.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Bookmark\Gateway
     */
    protected $innerGateway;

    /**
     * ExceptionConversion constructor.
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Bookmark\Gateway $innerGateway
     */
    public function __construct(Gateway $innerGateway)
    {
        $this->innerGateway = $innerGateway;
    }

    public function insertBookmark(Bookmark $bookmark): int
    {
        try {
            return $this->innerGateway->insertBookmark($bookmark);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    public function deleteBookmark($id)
    {
        try {
            return $this->innerGateway->deleteBookmark($id);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    public function loadBookmarkDataById($id)
    {
        try {
            return $this->innerGateway->loadBookmarkDataById($id);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    public function loadBookmarkDataByUserIdAndLocationId($userId, $locationId)
    {
        try {
            return $this->innerGateway->loadBookmarkDataByUserIdAndLocationId($userId, $locationId);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    public function getUserBookmarks($userId, $offset, $limit)
    {
        try {
            return $this->innerGateway->getUserBookmarks($userId, $offset, $limit);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }
}
