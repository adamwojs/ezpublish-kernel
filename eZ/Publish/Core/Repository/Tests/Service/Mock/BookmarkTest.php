<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\Repository\Tests\Service\Mock;

use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\PermissionResolver;
use eZ\Publish\Core\Repository\BookmarkService;
use eZ\Publish\Core\Repository\Tests\Service\Mock\Base as BaseServiceMockTest;
use eZ\Publish\Core\Repository\Values\Content\Location;
use eZ\Publish\Core\Repository\Values\User\UserReference;
use eZ\Publish\SPI\Persistence\Bookmark\Bookmark;
use PHPUnit\Framework\MockObject\MockObject;

class BookmarkTest extends BaseServiceMockTest
{
    const BOOKMARK_ID = 2;
    const CURRENT_USER_ID = 7;
    const LOCATION_ID = 1;

    /** @var \eZ\Publish\SPI\Persistence\Bookmark\Handler|\PHPUnit\Framework\MockObject\MockObject */
    private $bookmarkHandler;

    protected function setUp()
    {
        parent::setUp();

        $this->bookmarkHandler = $this->getPersistenceMockHandler('Bookmark\\Handler');
    }

    public function testCreateBookmark()
    {

    }

    /**
     * @covers \eZ\Publish\Core\Repository\BookmarkService::deleteBookmark
     */
    public function testDeleteBookmarkExisting()
    {
        $repository = $this->getRepositoryMock();

        $location = new Location([
            'id' => self::LOCATION_ID
        ]);

        $this->mockCurrentUserReference(new UserReference(self::CURRENT_USER_ID));
        $this->mockLoadLocation($repository, $location);

        $bookmark = new Bookmark([
            'id' => self::BOOKMARK_ID
        ]);

        $this->bookmarkHandler
            ->expects($this->once())
            ->method('loadByUserIdAndLocationId')
            ->with(self::CURRENT_USER_ID, self::LOCATION_ID)
            ->willReturn($bookmark);

        $repository->expects($this->once())->method('beginTransaction');

        $this->bookmarkHandler
            ->expects($this->once())
            ->method('delete')
            ->with($bookmark->id);

        $repository->expects($this->once())->method('commit');
        $repository->expects($this->never())->method('rollback');

        $this->createBookmarkService()->deleteBookmark($location);
    }

    /**
     * @covers \eZ\Publish\Core\Repository\BookmarkService::deleteBookmark
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testDeleteBookmarkNonExisting()
    {
        $repository = $this->getRepositoryMock();

        $location = new Location(['id' => self::LOCATION_ID]);

        $this->mockCurrentUserReference(new UserReference(self::CURRENT_USER_ID));
        $this->mockLoadLocation($repository, $location);

        $this->bookmarkHandler
            ->expects($this->once())
            ->method('loadByUserIdAndLocationId')
            ->with(self::CURRENT_USER_ID, self::LOCATION_ID)
            ->willThrowException($this->createMock(NotFoundException::class));

        $repository->expects($this->never())->method('beginTransaction');
        $this->bookmarkHandler->expects($this->never())->method('delete');
        $repository->expects($this->never())->method('rollback');
        $repository->expects($this->never())->method('commit');

        $this->createBookmarkService()->deleteBookmark($location);
    }

    public function testLoadBookmarks()
    {

    }

    /**
     * @covers \eZ\Publish\Core\Repository\BookmarkService::isBookmarked
     */
    public function testLocationShouldNotBeBookmarked()
    {
        $this->mockCurrentUserReference(new UserReference(self::CURRENT_USER_ID));

        $this->bookmarkHandler
            ->expects($this->once())
            ->method('loadByUserIdAndLocationId')
            ->with(self::CURRENT_USER_ID, self::LOCATION_ID)
            ->willThrowException($this->createMock(NotFoundException::class));

        $this->assertFalse($this->createBookmarkService()->isBookmarked(new Location([
            'id' => self::LOCATION_ID
        ])));
    }

    /**
     * @covers \eZ\Publish\Core\Repository\BookmarkService::isBookmarked
     */
    public function testLocationShouldBeBookmarked()
    {
        $this->mockCurrentUserReference(new UserReference(self::CURRENT_USER_ID));

        $this->bookmarkHandler
            ->expects($this->once())
            ->method('loadByUserIdAndLocationId')
            ->with(self::CURRENT_USER_ID, self::LOCATION_ID)
            ->willReturn($this->createMock(Bookmark::class));

        $this->assertTrue($this->createBookmarkService()->isBookmarked(new Location([
            'id' => self::LOCATION_ID
        ])));
    }

    private function mockCurrentUserReference(UserReference $currentUserReference): MockObject
    {
        $permissionResolverMock = $this->createMock(PermissionResolver::class);
        $permissionResolverMock
            ->expects($this->once())
            ->method('getCurrentUserReference')
            ->willReturn($currentUserReference);

        $repository = $this->getRepositoryMock();
        $repository
            ->expects($this->once())
            ->method('getPermissionResolver')
            ->willReturn($permissionResolverMock);

        return $permissionResolverMock;
    }

    private function mockLoadLocation(MockObject $repository, Location $location): MockObject
    {
        $locationServiceMock = $this->createMock(LocationService::class);
        $locationServiceMock
            ->expects($this->once())
            ->method('loadLocation')
            ->willReturn($location);

        $repository = $this->getRepositoryMock();
        $repository
            ->expects($this->once())
            ->method('getLocationService')
            ->willReturn($locationServiceMock);

        return $locationServiceMock;
    }

    /**
     * @return \eZ\Publish\API\Repository\BookmarkService|\PHPUnit\Framework\MockObject\MockObject
     */
    private function createBookmarkService(array $methods = null)
    {
        return $this
            ->getMockBuilder(BookmarkService::class)
            ->setConstructorArgs([$this->getRepositoryMock(), $this->bookmarkHandler])
            ->setMethods($methods)
            ->getMock();
    }
}
