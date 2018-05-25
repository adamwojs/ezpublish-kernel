<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Common\Output\Generator;
use eZ\Publish\Core\REST\Common\Output\Visitor;

class BookmarkList extends ValueObjectVisitor
{
    /**
     * {@inheritdoc}
     */
    public function visit(Visitor $visitor, Generator $generator, $data)
    {
        $generator->startObjectElement('BookmarkList');
        $visitor->setHeader('Content-Type', $generator->getMediaType('BookmarkList'));

//        $generator->startAttribute('href', $data->path);
//        $generator->endAttribute('href');

        $generator->startValueElement('count', $data->totalCount);
        $generator->endValueElement('count');

        $generator->startList('items');
        foreach ($data->items as $restLocation) {
            $generator->startObjectElement('Location');
            $visitor->visitValueObject($restLocation);
            $generator->endObjectElement('Location');
        }
        $generator->endList('items');

        $generator->endObjectElement('BookmarkList');
    }
}
