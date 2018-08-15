<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Legacy\ContentReview\Gateway\Doctrine\Table;

final class ReviewTable
{
    const NAME = 'ezcontentobject_review';

    const COLUMN_ID = 'id';
    const COLUMN_CONTENTOBJECT_ID = 'contentobject_id';
    const COLUMN_VERSION = 'version';
    const COLUMN_USER_ID = 'user_id';
    const COLUMN_STATUS = 'status';
}
