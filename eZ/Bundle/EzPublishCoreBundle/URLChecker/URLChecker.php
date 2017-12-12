<?php

namespace eZ\Bundle\EzPublishCoreBundle\URLChecker;

use DateTime;
use eZ\Publish\API\Repository\URLService as URLServiceInterface;
use eZ\Publish\API\Repository\Values\URL\SearchResult;
use eZ\Publish\API\Repository\Values\URL\URL;
use eZ\Publish\API\Repository\Values\URL\URLQuery;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

class URLChecker implements URLCheckerInterface
{
    use LoggerAwareTrait;

    /**
     * @var \eZ\Publish\API\Repository\URLService
     */
    protected $urlService;

    /**
     * @var \eZ\Bundle\EzPublishCoreBundle\URLChecker\URLHandlerRegistryInterface
     */
    protected $handlerRegistry;

    /**
     * URLChecker constructor.
     *
     * @param \eZ\Publish\API\Repository\URLService $urlService
     * @param \eZ\Bundle\EzPublishCoreBundle\URLChecker\URLHandlerRegistryInterface $handlerRegistry
     */
    public function __construct(
        URLServiceInterface $urlService,
        URLHandlerRegistryInterface $handlerRegistry)
    {
        $this->urlService = $urlService;
        $this->handlerRegistry = $handlerRegistry;
        $this->logger = new NullLogger();
    }

    /**
     * {@inheritdoc}
     */
    public function check(URLQuery $query)
    {
        $grouped = $this->fetchUrls($query);
        foreach ($grouped as $scheme => $urls) {
            if (!$this->handlerRegistry->supported($scheme)) {
                $this->logger->error('Unsupported URL schema: ' . $scheme);
                continue;
            }

            $handler = $this->handlerRegistry->getHandler($scheme);
            $handler->validate($urls, function (URL $url, $isValid) {
                $this->logger->info(sprintf('URL id = %d (%s) was checked (valid = %s)', $url->id, $url->url, (int) $isValid));
                $this->setUrlStatus($url, $isValid);
            });
        }
    }

    /**
     * Fetch URLs to check.
     *
     * @param \eZ\Publish\API\Repository\Values\URL\URLQuery $query
     * @return array
     */
    protected function fetchUrls(URLQuery $query)
    {
        return $this->groupByScheme(
            $this->urlService->findUrls($query)
        );
    }

    /**
     * Sets URL status.
     *
     * @param \eZ\Publish\API\Repository\Values\URL\URL $url
     * @param bool $isValid
     */
    protected function setUrlStatus(URL $url, $isValid)
    {
        $updateStruct = $this->urlService->createUpdateStruct();
        $updateStruct->isValid = $isValid;
        $updateStruct->lastChecked = new DateTime();

        $this->urlService->updateUrl($url, $updateStruct);
    }

    /**
     * Group URLs by schema.
     *
     * @param \eZ\Publish\API\Repository\Values\URL\SearchResult $urls
     * @return array
     */
    private function groupByScheme(SearchResult $urls)
    {
        $grouped = [];

        foreach ($urls as $url) {
            $scheme = parse_url($url->url, PHP_URL_SCHEME);
            if (!$scheme) {
                continue;
            }

            if (!isset($grouped[$scheme])) {
                $grouped[$scheme] = [];
            }

            $grouped[$scheme][] = $url;
        }

        return $grouped;
    }
}
