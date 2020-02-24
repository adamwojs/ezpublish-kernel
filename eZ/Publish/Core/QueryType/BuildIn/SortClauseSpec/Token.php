<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\QueryType\BuildIn\SortClauseSpec;

final class Token
{
    public const TYPE_NONE = 1;
    public const TYPE_ASC = 2;
    public const TYPE_DESC = 3;
    public const TYPE_ID = 4;
    public const TYPE_DOT = 5;
    public const TYPE_COMMA = 6;
    public const TYPE_INT = 7;
    public const TYPE_FLOAT = 8;
    public const TYPE_EOF = 9;

    /** @var int */
    private $type;

    /** @var string */
    private $value;

    public function __construct(int $type, string $value = '')
    {
        $this->type = $type;
        $this->value = $value;
    }

    public function is(int $type): bool
    {
        return $this->type === $type;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getValueAsFloat(): float
    {
        return (float)$this->value;
    }

    public function getValueAsInt(): int
    {
        return (int)$this->value;
    }
}
