<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\FieldType\GenericEntity;

use eZ\Publish\Core\FieldType\Value as BaseValue;

abstract class Value extends BaseValue
{
    public abstract function setFieldId(int $fieldId): void;

    public abstract function setVersionNo(int $versionNo): void;
}
