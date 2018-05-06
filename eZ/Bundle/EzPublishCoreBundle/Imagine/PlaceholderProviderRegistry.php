<?php

namespace eZ\Bundle\EzPublishCoreBundle\Imagine;

use InvalidArgumentException;

final class PlaceholderProviderRegistry
{
    /**
     * @var \eZ\Bundle\EzPublishCoreBundle\Imagine\PlaceholderProvider
     */
    private $providers;

    /**
     * PlaceholderProviderRegistry constructor.
     */
    public function __construct()
    {
        $this->providers = [];
    }

    public function addProvider(string $type, PlaceholderProvider $provider)
    {
        $this->providers[$type] = $provider;
    }

    public function supports(string $type): bool
    {
        return isset($this->providers[$type]);
    }

    public function getProvider(string $type): PlaceholderProvider
    {
        if (!$this->supports($type)) {
            throw new InvalidArgumentException("Unknown placeholder provider: $type");
        }

        return $this->providers[$type];
    }
}
