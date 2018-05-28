<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\REST\Server\Controller;

use eZ\Publish\API\Repository\BookmarkService;
use eZ\Publish\API\Repository\Exceptions\InvalidArgumentException;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\Core\REST\Common\Value as RestValue;
use eZ\Publish\Core\REST\Server\Values;
use eZ\Publish\Core\REST\Server\Controller as RestController;
use Symfony\Component\HttpFoundation\Request;

class Bookmark extends RestController
{
    /**
     * @var \eZ\Publish\API\Repository\BookmarkService
     */
    protected $bookmarkService;

    /**
     * @var \eZ\Publish\API\Repository\LocationService
     */
    protected $locationService;

    /**
     * Bookmark constructor.
     *
     * @param \eZ\Publish\API\Repository\BookmarkService $bookmarkService
     * @param \eZ\Publish\API\Repository\LocationService $locationService
     */
    public function __construct(BookmarkService $bookmarkService, LocationService $locationService)
    {
        $this->bookmarkService = $bookmarkService;
        $this->locationService = $locationService;
    }

    /**
     * Add given location to bookmarks.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string $locationPath
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     *
     * @return \eZ\Publish\Core\REST\Common\Value
     */
    public function createBookmark(Request $request, string $locationPath): RestValue
    {
        $location = $this->locationService->loadLocation(
            $this->extractLocationIdFromPath($locationPath)
        );

        try {
            $this->bookmarkService->createBookmark($location);

            return new Values\ResourceCreated(
                $this->router->generate(
                    'ezpublish_rest_loadLocation',
                    [
                        'locationPath' => trim($location->pathString, '/'),
                    ]
                )
            );
        } catch (InvalidArgumentException $e) {
            return new Values\Conflict();
        }
    }

    /**
     * Deletes a given bookmark.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string $locationPath
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     *
     * @return \eZ\Publish\Core\REST\Common\Value
     */
    public function deleteBookmark(Request $request, string $locationPath): RestValue
    {
        $location = $this->locationService->loadLocation(
            $this->extractLocationIdFromPath($locationPath)
        );

        try {
            $this->bookmarkService->deleteBookmark($location);

            return new Values\NoContent();
        } catch (InvalidArgumentException $e) {
            return new Values\Conflict();
        }
    }

    /**
     * Checks if given location is bookmarked.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string $locationPath
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     *
     * @return \eZ\Publish\Core\REST\Common\Value
     */
    public function isBookmarked(Request $request, string $locationPath): RestValue
    {
        $location = $this->locationService->loadLocation(
            $this->extractLocationIdFromPath($locationPath)
        );

        return new Values\LocationIsBookmarked(
            $location,
            $this->bookmarkService->isBookmarked($location)
        );
    }

    /**
     * List bookmarked locations.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     *
     * @return \eZ\Publish\Core\REST\Common\Value
     */
    public function loadBookmarks(Request $request): RestValue
    {
        $offset = $request->query->getInt('offset', 0);
        $limit = $request->query->getInt('limit', 25);

        $restLocations = [];
        $bookmarks = $this->bookmarkService->loadBookmarks($offset, $limit);
        foreach ($bookmarks as $bookmark) {
            $restLocations[] = new Values\RestLocation(
                $bookmark,
                $this->locationService->getLocationChildCount($bookmark)
            );
        }

        return new Values\BookmarkList($bookmarks->totalCount, $restLocations);
    }

    /**
     * Extracts and returns an item id from a path, e.g. /1/2/58 => 58.
     *
     * @param string $path
     *
     * @return mixed
     */
    private function extractLocationIdFromPath(string $path)
    {
        $pathParts = explode('/', $path);

        return array_pop($pathParts);
    }
}
