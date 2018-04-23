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
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * For demo purposes only: placeholders provided by http://placekitten.com.
 */
class PlacekittenProvider implements PlaceholderProvider
{
    const COLORFULL_URL = 'http://placekitten.com/%d/%d';
    const GRAYSCALE_URL = 'http://placekitten.com/g/%d/%d';

    /**
     * @var
     */
    private $options;

    /**
     * PlacekittenProvider constructor.
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->options = $this->resolveOptions($options);
    }

    public function getPlaceholder(ImageValue $value): string
    {
        $placekittenUrl = sprintf($this->options['url'], $value->width, $value->height);

        $path = $this->getTemporaryPath($value);
        file_put_contents($path, file_get_contents($placekittenUrl));

        return $path;
    }

    private function getTemporaryPath(ImageValue $value): string
    {
        return tempnam(sys_get_temp_dir(), 'placeholder') . '.' . pathinfo($value->id, PATHINFO_EXTENSION);
    }

    private function resolveOptions(array $options): array
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'url' => self::COLORFULL_URL,
        ]);

        return $resolver->resolve($options);
    }
}
