<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\FieldType\Generic\SettingsSchemaBuilder;

use eZ\Publish\Core\FieldType\Generic\SettingSchemaBuilder;

class ChildrenSchemaBuilder extends SettingSchemaBuilder implements NodeDefinition
{
    /** @var \eZ\Publish\Core\FieldType\Generic\SettingsSchemaBuilder\ArrayNodeDefinition */
    private $parent;

    public function __construct(ArrayNodeDefinition $parent)
    {
        $this->parent = $parent;
    }

    public function end(): ArrayNodeDefinition
    {
        return $this->parent;
    }
}
