<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Legacy\ContentReview\Gateway\Doctrine\Table;

final class CommentTable
{
    const NAME = 'ezcontentobject_review_comment';

    const COLUMN_ID = 'id';
    const COLUMN_REVIEW_ID = 'contentobject_review_id';
    const COLUMN_FIELD_DEFINITION_ID = 'contentclass_attribute_id';
    const COLUMN_COMMENT = 'comment';
    const COLUMN_LOCATION = 'location';
    const COLUMN_CREATED = 'created';
}
