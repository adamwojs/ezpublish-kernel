<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Imagine\PlaceholderProvider;

use eZ\Bundle\EzPublishCoreBundle\Imagine\PlaceholderProvider;
use eZ\Publish\Core\FieldType\Image\Value as ImageValue;
use RuntimeException;

/**
 * Remote placeholder provider e.g. http://placekitten.com.
 */
class RemoteProvider implements PlaceholderProvider
{
    /**
     * @var string
     */
    private $urlPattern;

    /**
     * @var int
     */
    private $timeout;

    /**
     * RemoteProvider constructor.
     *
     * @param string $urlPattern The url pattern
     * @param int $timeout The maximum number of seconds to allow downloading image
     */
    public function __construct(string $urlPattern, int $timeout = 5)
    {
        $this->urlPattern = $urlPattern;
        $this->timeout = $timeout;
    }

    /**
     * {@inheritdoc}
     */
    public function getPlaceholder(ImageValue $value): string
    {
        $path = $this->getTemporaryPath($value);
        $placeholderUrl = $this->getPlaceholderUrl($value);

        try {
            $handler = curl_init();

            curl_setopt_array($handler, [
                CURLOPT_URL => $placeholderUrl,
                CURLOPT_FILE => fopen($path, 'wb'),
                CURLOPT_TIMEOUT => $this->timeout,
                CURLOPT_FAILONERROR => true,
            ]);

            if (curl_exec($handler) === false) {
                throw new RuntimeException("Unable to download placeholder for {$value->id} ($placeholderUrl): " . curl_error($handler));
            }
        } finally {
            curl_close($handler);
        }

        return $path;
    }

    private function getPlaceholderUrl(ImageValue $value): string
    {
        return strtr($this->urlPattern, [
            '%id%' => $value->id,
            '%width%' => $value->width,
            '%height%' => $value->height,
        ]);
    }

    private function getTemporaryPath(ImageValue $value): string
    {
        return tempnam(sys_get_temp_dir(), 'placeholder') . '.' . pathinfo($value->id, PATHINFO_EXTENSION);
    }
}
