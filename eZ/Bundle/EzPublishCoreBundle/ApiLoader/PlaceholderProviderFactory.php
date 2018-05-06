<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\ApiLoader;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class PlaceholderProviderFactory implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @var \eZ\Bundle\EzPublishCoreBundle\Imagine\PlaceholderProvider[]
     */
    private $providersMap = [];

    public function setProvidersMap(array $providersMap)
    {
        $this->providersMap = $providersMap;
    }

    /**
     * Returns an instance of the requested placeholder provider.
     *
     * @param string $providerName
     * @return \eZ\Bundle\EzPublishCoreBundle\Imagine\PlaceholderProvider|null
     */
    public function getConfiguredPlaceholderProvider(string $providerName)
    {
        if (isset($this->providersMap[$providerName])) {
            return $this->container->get($this->providersMap[$providerName]);
        }

        return null;
    }
}
