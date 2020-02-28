<?php

declare(strict_types=1);

namespace eZ\Publish\Core\MVC\Symfony\View;

final class QueryView extends BaseView
{
    public const DEFAULT_ASSIGN_RESULTS_TO = 'items';

    /** @var string */
    private $queryType;

    /** @var array */
    private $queryParams;

    /** @var string */
    private $assignResultsTo = self::DEFAULT_ASSIGN_RESULTS_TO;

    public function getQueryType(): string
    {
        return $this->queryType;
    }

    public function setQueryType(string $queryType): void
    {
        $this->queryType = $queryType;
    }

    public function getQueryParams(): array
    {
        return $this->queryParams;
    }

    public function setQueryParams(array $queryParams): void
    {
        $this->queryParams = $queryParams;
    }

    public function getAssignResultsTo(): string
    {
        return $this->assignResultsTo;
    }

    public function setAssignResultsTo(string $assignResultsTo): void
    {
        $this->assignResultsTo = $assignResultsTo;
    }
}
