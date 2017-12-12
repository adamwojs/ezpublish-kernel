<?php

namespace eZ\Publish\Core\Persistence\Cache;

use eZ\Publish\API\Repository\Values\URL\URLQuery;
use eZ\Publish\SPI\Persistence\URL\Handler as URLHandlerInterface;
use eZ\Publish\SPI\Persistence\URL\URLUpdateStruct;
use eZ\Publish\SPI\Persistence\Handler as PersistenceHandler;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;

/**
 * SPI cache for URL Handler.
 *
 * @see \eZ\Publish\SPI\Persistence\URL\Handler
 */
class URLHandler implements URLHandlerInterface
{
    /**
     * @var \Symfony\Component\Cache\Adapter\TagAwareAdapterInterface
     */
    protected $cache;

    /**
     * @var \eZ\Publish\SPI\Persistence\Handler
     */
    protected $persistenceHandler;

    /**
     * @var \eZ\Publish\Core\Persistence\Cache\PersistenceLogger
     */
    protected $logger;

    /**
     * Setups current handler with everything needed.
     *
     * @param \Symfony\Component\Cache\Adapter\TagAwareAdapterInterface $cache
     * @param \eZ\Publish\SPI\Persistence\Handler $persistenceHandler
     * @param \eZ\Publish\Core\Persistence\Cache\PersistenceLogger $logger
     */
    public function __construct(
        TagAwareAdapterInterface $cache,
        PersistenceHandler $persistenceHandler,
        PersistenceLogger $logger)
    {
        $this->cache = $cache;
        $this->persistenceHandler = $persistenceHandler;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function updateUrl($id, URLUpdateStruct $struct)
    {
        $this->logger->logCall(__METHOD__, [
            'url' => $id,
            'struct' => $struct,
        ]);

        $url = $this->persistenceHandler->urlHandler()->updateUrl($id, $struct);

        $this->cache->invalidateTags(['url-' . $id]);

        return $url;
    }

    /**
     * {@inheritdoc}
     */
    public function find(URLQuery $query)
    {
        $this->logger->logCall(__METHOD__, [
            'query' => $query,
        ]);

        return $this->persistenceHandler->urlHandler()->find($query);
    }

    /**
     * {@inheritdoc}
     */
    public function loadById($id)
    {
        $cacheItem = $this->cache->getItem('ez-url-' . $id);

        $url = $cacheItem->get();
        if ($cacheItem->isHit()) {
            return $url;
        }

        $this->logger->logCall(__METHOD__, ['url' => $id]);
        $url = $this->persistenceHandler->urlHandler()->loadById($id);

        $cacheItem->set($url);
        $cacheItem->tag(['url-' . $id]);
        $this->cache->save($cacheItem);

        return $url;
    }

    /**
     * {@inheritdoc}
     */
    public function loadByUrl($url)
    {
        return $this->persistenceHandler->urlHandler()->loadByUrl($url);
    }

    /**
     * {@inheritdoc}
     */
    public function findUsages($id)
    {
        $this->logger->logCall(__METHOD__, ['url' => $id]);
        return $this->persistenceHandler->urlHandler()->findUsages($id);
    }
}
