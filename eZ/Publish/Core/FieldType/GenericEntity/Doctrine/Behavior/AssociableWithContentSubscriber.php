<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\FieldType\GenericEntity\Doctrine\Behavior;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use ReflectionClass;

class AssociableWithContentSubscriber implements EventSubscriber
{
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs): void
    {
        $metadata = $eventArgs->getClassMetadata();

        if (null === $metadata->reflClass) {
            return;
        }

        if ($this->isAssociatedWithContent($metadata)) {
            $this->mapEntity($metadata);
        }
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::loadClassMetadata,
        ];
    }

    private function isAssociatedWithContent(ClassMetadata $metadata): bool
    {
        return $this->hasTrait($metadata->reflClass, AssociableWithContent::class);
    }

    private function hasTrait(ReflectionClass $class, string $trait): bool
    {
        if (in_array($trait, $class->getTraitNames())) {
            return true;
        }

        $parentClass = $class->getParentClass();
        if (false === $parentClass || null === $parentClass) {
            return false;
        }

        return $this->hasTrait($parentClass, $trait);
    }

    private function mapEntity(ClassMetadata $metadata): void
    {
        if (!$metadata->hasField('fieldId')) {
            $metadata->mapField([
                'fieldName' => 'fieldId',
                'type' => 'integer',
                'nullable' => true,
            ]);
        }

        if (!$metadata->hasField('versionNo')) {
            $metadata->mapField([
                'fieldName' => 'versionNo',
                'type' => 'integer',
                'nullable' => true,
            ]);
        }
    }
}
