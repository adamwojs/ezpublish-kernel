<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\FieldType\Generic\Indexable;

/**
 * @Annotation
 * @Target({"METHOD","PROPERTY"})
 */
class Field
{
    /** @var string|null */
    public $name = null;

    /** @var string */
    public $type;

    /**
     * Whether highlighting should be performed for this field on result documents.
     *
     * @var bool
     */
    public $highlight = false;

    /**
     * The importance of that field (boost factor).
     *
     * @var int
     */
    public $boost = 1;

    /**
     * Whether the field supports multiple values.
     *
     * @var bool
     */
    public $multiValue = false;

    /**
     * Whether the field should be a part of the resulting document.
     *
     * @var bool
     */
    public $inResult = true;

    /** @var bool */
    public $isDefaultMatch = false;

    /** @var bool */
    public $isDefaultSortField = false;
}
