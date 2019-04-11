<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\FieldType\Generic;

use eZ\Publish\Core\FieldType\Generic\SettingsSchemaBuilder\NodeDefinition;
use eZ\Publish\Core\FieldType\Generic\SettingsSchemaBuilder\ArrayNodeDefinition;
use eZ\Publish\Core\FieldType\Generic\SettingsSchemaBuilder\ScalarNodeDefinition;

class SettingSchemaBuilder
{
    /** @var array */
    private $children;

    public function scalarNode(string $name): ScalarNodeDefinition
    {
        if (!isset($this->children[$name])) {
            $this->children[$name] = new ScalarNodeDefinition($this);
        }

        return $this->children[$name];
    }

    public function arrayNode(string $name): ArrayNodeDefinition
    {
        if (!isset($this->children[$name])) {
            $this->children[$name] = new ArrayNodeDefinition($this);
        }

        return $this->children[$name];
    }

    public function buildSchema(): array
    {
        $schema = [];

        foreach ($this->children as $name => $builder) {
            $schema[$name] = $builder->buildSchema();
        }

        return $schema;
    }

}
