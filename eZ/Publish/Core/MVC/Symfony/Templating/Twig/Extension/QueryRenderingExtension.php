<?php

declare(strict_types=1);

namespace eZ\Publish\Core\MVC\Symfony\Templating\Twig\Extension;

use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Component\HttpKernel\Fragment\FragmentHandler;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class QueryRenderingExtension extends AbstractExtension
{
    /** @var \Symfony\Component\HttpKernel\Fragment\FragmentHandler */
    private $fragmentHandler;

    public function __construct(FragmentHandler $fragmentHandler)
    {
        $this->fragmentHandler = $fragmentHandler;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'ez_render_*_query',
                function (string $type, array $options) {
                    return $this->fragmentHandler->render(
                        $this->createControllerReference($type, $options),
                        'inline'
                    );
                },
                [
                    'is_safe' => [
                        'html'
                    ],
                ]
            ),
            new TwigFunction(
                'ez_render_*_query_esi',
                function (string $type, array $options) {
                    return $this->fragmentHandler->render(
                        $this->createControllerReference($type, $options),
                        'esi'
                    );
                },
                [
                    'is_safe' => [
                        'html'
                    ],
                ]
            ),
            new TwigFunction(
                'ez_render_content',
                function (array $attributes, array $query = []) {
                    return $this->fragmentHandler->render(
                        new ControllerReference('ez_content::viewAction', $attributes, $query),
                        'esi'
                    );
                },
                [
                    'is_safe' => [
                        'html'
                    ],
                ]
            )
        ];
    }

    private function createControllerReference(string $type, array $options): ControllerReference
    {
        switch ($type) {
            case 'content':
                $controller = 'ez_query_render::renderContentQueryAction';
                break;
            case 'content_info':
                $controller = 'ez_query_render::renderContentInfoQueryAction';
                break;
            case 'location':
                $controller = 'ez_query_render::renderLocationQueryAction';
                break;
            default:
                throw new InvalidArgumentException('$type', '???');
        }

        return new ControllerReference($controller, [
            'options' => $options
        ]);
    }
}
