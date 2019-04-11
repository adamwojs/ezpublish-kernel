<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\FieldType\Generic\SettingsSchemaBuilder;

use eZ\Publish\Core\FieldType\Generic\SettingSchemaBuilder;

class ScalarNodeDefinition implements NodeDefinition
{
    /** @var \eZ\Publish\Core\FieldType\Generic\SettingSchemaBuilder  */
    private $parent;

    /** @var mixed */
    private $defaultValue;

    /** @var array */
    private $constraints;

    public function __construct(SettingSchemaBuilder $parent)
    {
        $this->parent = $parent;
    }

    public function defaultValue($defaultValue): self
    {
        $this->defaultValue = $defaultValue;
        return $this;
    }

    public function constraints(array $constraints): self
    {
        $this->constraints = $constraints;
        return $this;
    }

    public function end(): SettingSchemaBuilder
    {
        return $this->parent;
    }

    public function buildSchema(): array
    {
        return [
            'default' => $this->defaultValue,
            'constraints' => $this->constraints
        ];
    }
}
