<?php

namespace eZ\Publish\Core\MVC\Symfony\Cache\Content;

use Symfony\Component\HttpFoundation\Response;

class TtlResolver implements TtlResolverInterface
{
    /**
     * Default TTL for TTL cache.
     *
     * @var array
     */
    private $defaultTtl;

    public function __construct(array $defaultTtl)
    {
        $this->defaultTtl = $defaultTtl;
    }

    public function resolveTtl(Response $response)
    {
        $statusCode = $response->getStatusCode();

        if (isset($this->defaultTtl[$statusCode])) {
            return $this->defaultTtl[$statusCode];
        }

        return $this->defaultTtl[$this->getWildcardForStatusCode($statusCode)];
    }

    /**
     * Returns the wildcard for the status code class
     *
     * @param int $statusCode Status code value
     * @return string
     */
    protected function getWildcardForStatusCode($statusCode)
    {
        return substr($statusCode, 0, 1) . 'XX';
    }
}
