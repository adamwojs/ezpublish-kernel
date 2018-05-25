<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Output\Generator;
use eZ\Publish\Core\REST\Common\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Common\Output\Visitor;
use eZ\Publish\Core\REST\Server\Values\LocationIsBookmarked as LocationIsBookmarkedValue;

class LocationIsBookmarked extends ValueObjectVisitor
{
    /**
     * {@inheritdoc}
     */
    public function visit(Visitor $visitor, Generator $generator, $data)
    {
        $generator->startObjectElement('LocationIsBookmarked');
        $visitor->setHeader('Content-Type', $generator->getMediaType('LocationIsBookmarked'));
        $this->visitAttributes($visitor, $generator, $data);
        $generator->endObjectElement('LocationIsBookmarked');
    }

    protected function visitAttributes(Visitor $visitor, Generator $generator, LocationIsBookmarkedValue $data): void
    {
        $generator->startAttribute(
            'href',
            $this->router->generate(
                'ezpublish_rest_isBookmarked',
                array('locationPath' => trim($data->location->pathString, '/'))
            )
        );
        $generator->endAttribute('href');

        $generator->startValueElement('isBookmarked', $data->isBookmarked);
        $generator->endValueElement('isBookmarked');
    }
}
