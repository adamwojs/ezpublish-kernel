<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\EventListener;

use eZ\Publish\Core\MVC\Symfony\Cache\Content\TtlResolverInterface;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\Core\MVC\Symfony\View\CachableView;
use eZ\Publish\Core\MVC\Symfony\View\LocationValueView;
use eZ\Publish\Core\MVC\Symfony\View\View;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Configures the Response cache properties.
 */
class CacheViewResponseListener implements EventSubscriberInterface
{
    /**
     * @var eZ\Publish\Core\MVC\Symfony\Cache\Content\TtlResolverInterface
     */
    private $ttlResolver;

    /**
     * True if view cache is enabled, false if it is not.
     *
     * @var bool
     */
    private $enableViewCache;

    /**
     * True if TTL cache is enabled, false if it is not.
     *
     * @var bool
     */
    private $enableTtlCache;

    public function __construct(TtlResolverInterface $ttlResolver, $enableViewCache, $enableTtlCache)
    {
        $this->ttlResolver = $ttlResolver;
        $this->enableViewCache = $enableViewCache;
        $this->enableTtlCache = $enableTtlCache;
    }

    public static function getSubscribedEvents()
    {
        return [KernelEvents::RESPONSE => 'configureCache'];
    }

    public function configureCache(FilterResponseEvent $event)
    {
        if (!($view = $event->getRequest()->attributes->get('view')) instanceof CachableView) {
            return;
        }

        if (!$this->enableViewCache || !$view->isCacheEnabled()) {
            return;
        }

        $response = $event->getResponse();

        if ($view instanceof LocationValueView && ($location = $view->getLocation()) instanceof Location) {
            $response->headers->set('X-Location-Id', $location->id, false);
        }

        $response->setPublic();
        if ($this->enableTtlCache && !$response->headers->hasCacheControlDirective('s-maxage')) {
            $response->setSharedMaxAge($this->ttlResolver->resolveTtl($response));
        }
    }
}
