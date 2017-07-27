<?php

namespace eZ\Publish\Core\MVC\Symfony\Cache\Content;

use Symfony\Component\HttpFoundation\Response;

interface TtlResolverInterface
{
    /**
     * Resolve TTL value based on response object
     *
     * @param \Symfony\Component\HttpFoundation\Response $response
     * @return int
     */
    public function resolveTtl(Response $response);
}
