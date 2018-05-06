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
use Imagine\Image as Image;
use Imagine\Image\ImagineInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GenericProvider implements PlaceholderProvider
{
    /**
     * @var \Imagine\Image\ImagineInterface
     */
    private $imagine;

    /**
     * @var array
     */
    private $options;

    /**
     * GenericProvider constructor.
     *
     * @param \Imagine\Image\ImagineInterface $imagine
     * @param array $options
     */
    public function __construct(ImagineInterface $imagine, array $options = [])
    {
        $this->imagine = $imagine;
        $this->options = $this->resolveOptions($options);
    }

    public function getPlaceholder(ImageValue $value): string
    {
        $palette = new Image\Palette\RGB();
        $background = $palette->color($this->options['background']);
        $foreground = $palette->color($this->options['foreground']);
        $secondary = $palette->color($this->options['secondary']);

        $size = new Image\Box($value->width, $value->height);
        $font = $this->imagine->font($this->options['fontpath'], $this->options['fontsize'], $foreground);
        $text = $this->getPlaceholderText($value);

        $center = new Image\Point\Center($size);
        $textbox = $font->box($text);
        $textpos = new Image\Point(
            max($center->getX() - ($textbox->getWidth() / 2), 0),
            max($center->getY() - ($textbox->getHeight() / 2), 0)
        );

        $image = $this->imagine->create($size, $background);
        $image->draw()->line(
            new Image\Point(0, 0),
            new Image\Point($value->width, $value->height),
            $secondary
        );

        $image->draw()->line(
            new Image\Point($value->width, 0),
            new Image\Point(0, $value->height),
            $secondary
        );

        $image->draw()->text($text, $font, $textpos, 0, $value->width);

        $path = $this->getTemporaryPath();
        $image->save($path, [
            'format' => pathinfo($value->id, PATHINFO_EXTENSION)
        ]);

        return $path;
    }

    public function setOptions(array $options)
    {
        $this->options = $this->resolveOptions($options);
    }

    private function getPlaceholderText(ImageValue $value): string
    {
        return strtr($this->options['text'], [
            '%width%' => $value->width,
            '%height%' => $value->height,
            '%id%' => $value->id,
        ]);
    }

    private function getTemporaryPath(): string
    {
        return stream_get_meta_data(tmpfile())['uri'];
    }

    private function resolveOptions(array $options): array
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'background' => '#EEEEEE',
            'foreground' => '#000000',
            'secondary' => '#CCCCCC',
            'fontsize' => 20,
            'text' => "IMAGE PLACEHOLDER %width%x%height%\n(%id%)",
            // (!) Default font is not part of this PR
            'fontpath' => __DIR__ . '/../../Resources/font/font.ttf',
        ]);

        return $resolver->resolve($options);
    }
}
