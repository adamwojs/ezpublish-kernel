<?php

namespace eZ\Publish\Core\MVC\Symfony\Cache\Tests\Content;

use eZ\Publish\Core\MVC\Symfony\Cache\Content\TtlResolver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class TtlResolverTest extends TestCase
{
    /**
     * @dataProvider resolveTtlProvider
     */
    public function testResolveTtl($configuration, $statusCode, $expectedTtl)
    {
        $response = $this->getMock(Response::class);
        $response
            ->expects($this->atLeastOnce())
            ->method('getStatusCode')
            ->willReturn($statusCode);

        $ttlResolver = new TtlResolver($configuration);

        $this->assertSame($expectedTtl, $ttlResolver->resolveTtl($response));
    }

    public function resolveTtlProvider()
    {
        $configuration = [
            '200' => 180,
            '2XX' => 60
        ];

        return [
            [ $configuration, 200, 180 ],
            [ $configuration, 201, 60 ]
        ];
    }
}
