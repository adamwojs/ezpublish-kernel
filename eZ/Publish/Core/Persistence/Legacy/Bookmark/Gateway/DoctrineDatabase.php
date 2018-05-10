<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Bookmark\Gateway;

use Doctrine\DBAL\Driver\Connection;
use eZ\Publish\Core\Persistence\Legacy\Bookmark\Gateway;
use eZ\Publish\SPI\Persistence\Bookmark\Bookmark;
use PDO;

class DoctrineDatabase extends Gateway
{
    const TABLE_BOOKMARKS = 'ezcontentbrowsebookmark';

    const COLUMN_ID = 'id';
    const COLUMN_USER_ID = 'user_id';
    const COLUMN_LOCATION_ID = 'node_id';
    const COLUMN_NAME = 'name';

    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function insertBookmark(Bookmark $bookmark): int
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->insert(self::TABLE_BOOKMARKS)
            ->values([
                self::COLUMN_NAME => ':name',
                self::COLUMN_USER_ID => ':user_id',
                self::COLUMN_LOCATION_ID => ':location_id',
            ])
            ->setParameter(':name', $bookmark->name, PDO::PARAM_STR)
            ->setParameter(':user_id', $bookmark->userId, PDO::PARAM_INT)
            ->setParameter(':location_id', $bookmark->locationId, PDO::PARAM_INT);

        $query->execute();

        return $this->connection->lastInsertId();
    }

    public function deleteBookmark($id)
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->delete(self::TABLE_BOOKMARKS)
            ->where($query->expr()->eq(self::COLUMN_ID, ':id'))
            ->setParameter(':id', $id, PDO::PARAM_INT);

        $query->execute();
    }

    public function loadBookmarkDataById($id)
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->select(
                self::COLUMN_ID,
                self::COLUMN_NAME,
                self::COLUMN_USER_ID,
                self::COLUMN_LOCATION_ID
            )
            ->from(self::TABLE_BOOKMARKS)
            ->where($query->expr()->eq(self::COLUMN_ID, ':id'))
            ->setParameter(':id', $id, PDO::PARAM_INT);

        return $query->execute()->fetchAll(PDO::FETCH_ASSOC);
    }

    public function loadBookmarkDataByUserIdAndLocationId($userId, $locationId)
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->select(
                self::COLUMN_ID,
                self::COLUMN_NAME,
                self::COLUMN_USER_ID,
                self::COLUMN_LOCATION_ID
            )
            ->from(self::TABLE_BOOKMARKS)
            ->where($query->expr()->andX(
                $query->expr()->eq(self::COLUMN_USER_ID, ':user_id'),
                $query->expr()->eq(self::COLUMN_LOCATION_ID, ':location_id')
            ))
            ->setParameter(':user_id', $userId, PDO::PARAM_INT)
            ->setParameter(':location_id', $locationId, PDO::PARAM_INT);

        return $query->execute()->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUserBookmarks($userId, $offset = 0, $limit = -1)
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->select(
                self::COLUMN_ID,
                self::COLUMN_NAME,
                self::COLUMN_USER_ID,
                self::COLUMN_LOCATION_ID
            )
            ->from(self::TABLE_BOOKMARKS)
            ->where($query->expr()->eq(self::COLUMN_USER_ID, ':user_id'))
            ->setFirstResult($offset);

        if ($limit > -1) {
            $query->setMaxResults($limit);
        }

        $query->orderBy(self::COLUMN_ID, 'DESC');
        $query->setParameter(':user_id', $userId, PDO::PARAM_INT);

        return $query->execute()->fetchAll(PDO::FETCH_ASSOC);
    }
}
