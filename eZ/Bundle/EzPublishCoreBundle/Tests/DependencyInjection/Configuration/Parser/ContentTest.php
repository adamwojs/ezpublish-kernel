<?php

/**
 * File containing the ContentTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Configuration\Parser;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\EzPublishCoreExtension;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Parser\Content as ContentConfigParser;
use Symfony\Component\Yaml\Yaml;

class ContentTest extends AbstractParserTestCase
{
    protected function getContainerExtensions()
    {
        return array(
            new EzPublishCoreExtension(array(new ContentConfigParser())),
        );
    }

    protected function getMinimalConfiguration()
    {
        return Yaml::parse(file_get_contents(__DIR__ . '/../../Fixtures/ezpublish_minimal.yml'));
    }

    public function testDefaultContentSettings()
    {
        $this->load();

        $this->assertConfigResolverParameterValue('content.view_cache', true, 'ezdemo_site');
        $this->assertConfigResolverParameterValue('content.ttl_cache', true, 'ezdemo_site');
        $this->assertConfigResolverParameterValue('content.default_ttl', 60, 'ezdemo_site');
    }

    /**
     * @dataProvider contentSettingsProvider
     */
    public function testContentSettings(array $config, array $expected)
    {
        $this->load(
            array(
                'system' => array(
                    'ezdemo_site' => $config,
                ),
            )
        );

        foreach ($expected as $key => $val) {
            $this->assertConfigResolverParameterValue($key, $val, 'ezdemo_site', false, !is_array($val));
        }
    }

    public function contentSettingsProvider()
    {
        return [
            [
                [
                    'content' => [
                        'view_cache' => true,
                        'ttl_cache' => true,
                        'default_ttl' => 100,
                    ],
                ],
                [
                    'content.view_cache' => true,
                    'content.ttl_cache' => true,
                    'content.default_ttl' => [
                        '1XX' => 100,
                        '2XX' => 100,
                        '3XX' => 100,
                        '4XX' => 100,
                        '5XX' => 100
                    ],
                ],
            ],
            [
                [
                    'content' => [
                        'view_cache' => false,
                        'ttl_cache' => false,
                        'default_ttl' => 123,
                    ],
                ],
                [
                    'content.view_cache' => false,
                    'content.ttl_cache' => false,
                    'content.default_ttl' => [
                        '1XX' => 123,
                        '2XX' => 123,
                        '3XX' => 123,
                        '4XX' => 123,
                        '5XX' => 123
                    ],
                ],
            ],
            [
                [
                    'content' => [
                        'view_cache' => false,
                    ],
                ],
                [
                    'content.view_cache' => false,
                    'content.ttl_cache' => true,
                    'content.default_ttl' => [
                        '1XX' => 60,
                        '2XX' => 60,
                        '3XX' => 60,
                        '4XX' => 60,
                        '5XX' => 60
                    ],
                ],
            ],
            [
                [
                    'content' => [
                        'tree_root' => ['location_id' => 123],
                    ],
                ],
                [
                    'content.view_cache' => true,
                    'content.ttl_cache' => true,
                    'content.default_ttl' => [
                        '1XX' => 60,
                        '2XX' => 60,
                        '3XX' => 60,
                        '4XX' => 60,
                        '5XX' => 60
                    ],
                    'content.tree_root.location_id' => 123,
                ],
            ],
            [
                [
                    'content' => [
                        'tree_root' => [
                            'location_id' => 456,
                            'excluded_uri_prefixes' => ['/media/images', '/products'],
                        ],
                    ],
                ],
                [
                    'content.view_cache' => true,
                    'content.ttl_cache' => true,
                    'content.default_ttl' => [
                        '1XX' => 60,
                        '2XX' => 60,
                        '3XX' => 60,
                        '4XX' => 60,
                        '5XX' => 60
                    ],
                    'content.tree_root.location_id' => 456,
                    'content.tree_root.excluded_uri_prefixes' => ['/media/images', '/products'],
                ],
            ],
            [
                [
                    'content' => [
                        'view_cache' => true,
                        'ttl_cache' => true,
                        'default_ttl' => [
                            '1XX' => 30
                        ],
                    ],
                ],
                [
                    'content.view_cache' => true,
                    'content.ttl_cache' => true,
                    'content.default_ttl' => [
                        '1XX' => 30,
                        '2XX' => 60,
                        '3XX' => 60,
                        '4XX' => 60,
                        '5XX' => 60
                    ],
                ],
            ],
            [
                [
                    'content' => [
                        'view_cache' => true,
                        'ttl_cache' => true,
                        'default_ttl' => [],
                    ],
                ],
                [
                    'content.view_cache' => true,
                    'content.ttl_cache' => true,
                    'content.default_ttl' => [
                        '1XX' => 60,
                        '2XX' => 60,
                        '3XX' => 60,
                        '4XX' => 60,
                        '5XX' => 60
                    ],
                ],
            ],
            [
                [
                    'content' => [
                        'view_cache' => true,
                        'ttl_cache' => true,
                        'default_ttl' => [
                            '302' => 0,
                            '3XX' => 60,
                            '404' => 60,
                            '4XX' => 30,
                            '5XX' => 10
                        ],
                    ],
                ],
                [
                    'content.view_cache' => true,
                    'content.ttl_cache' => true,
                    'content.default_ttl' => [
                        '302' => 0,
                        '3XX' => 60,
                        '404' => 60,
                        '4XX' => 30,
                        '1XX' => 60,
                        '2XX' => 60,
                        '5XX' => 10,
                    ],
                ],
            ],
        ];
    }
}
