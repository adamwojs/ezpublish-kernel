<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\FieldType\Generic\SettingsSchemaBuilder;

use eZ\Publish\Core\FieldType\Generic\SettingSchemaBuilder;

class ArrayNodeDefinition implements NodeDefinition
{
    /** @var \eZ\Publish\Core\FieldType\Generic\SettingsSchemaBuilder\NodeDefinition  */
    private $parent;

    /** @var \eZ\Publish\Core\FieldType\Generic\SettingsSchemaBuilder\NodeDefinition */
    private $childrenBuilder;

    public function __construct(SettingSchemaBuilder $parent)
    {
        $this->parent = $parent;
        $this->childrenBuilder = new ChildrenSchemaBuilder($this);
    }

    public function children(): ChildrenSchemaBuilder
    {
        return $this->childrenBuilder;
    }

    public function end(): SettingSchemaBuilder
    {
        return $this->parent;
    }

    public function buildSchema(): array
    {
        return $this->childrenBuilder->buildSchema();
    }
}
