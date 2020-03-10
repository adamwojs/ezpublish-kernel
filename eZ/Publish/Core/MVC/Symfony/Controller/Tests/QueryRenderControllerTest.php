<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\MVC\Symfony\Controller\Tests;

use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\Core\MVC\Symfony\Controller\QueryRenderController;
use eZ\Publish\Core\MVC\Symfony\View\QueryView;
use eZ\Publish\Core\Pagination\Pagerfanta\ContentSearchHitAdapter;
use eZ\Publish\Core\QueryType\QueryType;
use eZ\Publish\Core\QueryType\QueryTypeRegistry;
use Pagerfanta\Pagerfanta;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class QueryRenderControllerTest extends TestCase
{
    private const MINIMAL_OPTIONS = [
        'query' => [
            'query_type' => 'ExampleQuery',
        ],
        'template' => 'example.html.twig'
    ];

    /** @var \eZ\Publish\API\Repository\SearchService */
    private $searchService;

    /** @var \eZ\Publish\Core\QueryType\QueryTypeRegistry */
    private $queryTypeRegistry;

    /** @var \eZ\Publish\Core\MVC\Symfony\Controller\QueryRenderController */
    private $controller;

    public function testRenderContentQueryAction(): void
    {
        $options = self::MINIMAL_OPTIONS;

        $query = new Query();

        $queryType = $this->createMock(QueryType::class);
        $queryType
            ->method('getQuery')
            ->with($options['query']['parameters'] ?? [])
            ->willReturn($query);

        $this->queryTypeRegistry
            ->method('getQueryType')
            ->with($options['query']['query_type'] ?? null)
            ->willReturn($queryType);

        $pagerfanta = new Pagerfanta(new ContentSearchHitAdapter($query, $this->searchService));
        $pagerfanta->setAllowOutOfRangePages(true);

        $this->assertEquals(
            new QueryView('example.html.twig', [
                'items' => $pagerfanta,
            ]),
            $this->controller->renderContentQueryAction(new Request(), $options)
        );
    }

    public function testRenderContentInfoQueryAction(Request $request, array $options, QueryView $expectedQueryView): void
    {
        $this->assertEquals(
            $expectedQueryView,
            $this->controller->renderContentInfoQueryAction($request, $options)
        );
    }

    public function dataProviderForRenderContentInfoQueryAction(): iterable
    {
        return [];
    }

    /**
     * @dataProvider dataProviderForRenderLocationQueryAction
     */
    public function testRenderLocationQueryAction(Request $request, array $options, QueryView $expectedQueryView): void
    {
        $this->assertEquals(
            $expectedQueryView,
            $this->controller->renderLocationQueryAction($request, $options)
        );
    }

    public function dataProviderForRenderLocationQueryAction(): iterable
    {
        return [];
    }

    protected function setUp(): void
    {
        $this->searchService = $this->createMock(SearchService::class);
        $this->queryTypeRegistry = $this->createMock(QueryTypeRegistry::class);

        $this->controller = new QueryRenderController(
            $this->searchService,
            $this->queryTypeRegistry
        );
    }
}
