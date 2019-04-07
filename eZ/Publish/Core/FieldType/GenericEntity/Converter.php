<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\FieldType\GenericEntity;

use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter as ConverterInterface;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition;

class Converter implements ConverterInterface
{
    public function toStorageValue(FieldValue $value, StorageFieldValue $storageFieldValue)
    {
    }

    public function toFieldValue(StorageFieldValue $value, FieldValue $fieldValue)
    {
    }

    public function toStorageFieldDefinition(FieldDefinition $fieldDef, StorageFieldDefinition $storageDef)
    {
        // TODO: Implement \eZ\Publish\Core\FieldType\GenericEntity\Converter::toStorageFieldDefinition
    }

    public function toFieldDefinition(StorageFieldDefinition $storageDef, FieldDefinition $fieldDef)
    {
        // TODO: Implement \eZ\Publish\Core\FieldType\GenericEntity\Converter::toFieldDefinition
    }

    public function getIndexColumn()
    {
        return false;
    }
}
