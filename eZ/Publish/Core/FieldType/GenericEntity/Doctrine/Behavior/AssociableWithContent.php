<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\FieldType\GenericEntity\Doctrine\Behavior;

trait AssociableWithContent
{
    protected $fieldId;

    protected $versionNo;

    public function getFieldId()
    {
        return $this->fieldId;
    }

    public function setFieldId($fieldId)
    {
        $this->fieldId = $fieldId;
    }

    public function getVersionNo()
    {
        return $this->versionNo;
    }

    public function setVersionNo($versionNo)
    {
        $this->versionNo = $versionNo;
    }

    public function isAssociatedWithContent(): bool
    {
        return true;
    }
}
