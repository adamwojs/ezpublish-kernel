<?php

declare(strict_types=1);

namespace eZ\Publish\Core\MVC\Symfony\Controller\Content;

use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\Core\MVC\Symfony\View\QueryView;
use eZ\Publish\Core\QueryType\QueryTypeRegistry;

/**
 * @see ez_render_query
 */
final class QueryRenderController
{
    /** @var \eZ\Publish\API\Repository\SearchService */
    private $searchService;

    /** @var \eZ\Publish\Core\QueryType\QueryTypeRegistry */
    private $queryTypeRegistry;

    public function __construct(SearchService $searchService, QueryTypeRegistry $queryTypeRegistry)
    {
        $this->searchService = $searchService;
        $this->queryTypeRegistry = $queryTypeRegistry;
    }

    public function renderContentQueryAction(array $options): QueryView
    {
        return $this->renderQuery($options, 'findContent');
    }

    public function renderContentInfoQueryAction(array $options): QueryView
    {
        return $this->renderQuery($options, 'findContentInfo');
    }

    public function renderLocationQueryAction(array $options): QueryView
    {
        return $this->renderQuery($options, 'findLocation');
    }

    private function renderQuery(array $options, string $method): QueryView
    {
        $queryType = $this->queryTypeRegistry->getQueryType(
            $options['query']['query_type']
        );

        $searchResults = $this->searchService->{$method}(
            $queryType->getQuery($options['query']['parameters'])
        );

        $view = new QueryView();
        $view->setTemplateIdentifier($options['template']);
        $view->setAssignResultsTo($options['query']['assign_results_to']);
        $view->addParameters([
            $view->getAssignResultsTo() => $searchResults
        ]);

        return $view;
    }
}
