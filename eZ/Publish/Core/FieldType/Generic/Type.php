<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\FieldType\Generic;

use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\Core\FieldType\FieldType;
use eZ\Publish\Core\FieldType\ValidationError;
use eZ\Publish\Core\FieldType\Value as BaseValue;
use eZ\Publish\SPI\FieldType\Value as SPIValue;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class Type extends FieldType
{
    /** @var \Symfony\Component\Serializer\SerializerInterface */
    protected $serializer;

    /** @var \Symfony\Component\Validator\Validator\ValidatorInterface */
    protected $validator;

    public function __construct(Serializer $serializer, ValidatorInterface $validator)
    {
        $this->serializer = $serializer;
        $this->validator = $validator;
    }

    public function getEmptyValue()
    {
        $class = $this->getValueClass();

        return new $class();
    }

    public function fromHash($hash)
    {
        if ($hash) {
            return $this->serializer->denormalize($hash, $this->getValueClass(), 'json');
        }

        return $this->getEmptyValue();
    }

    public function toHash(SPIValue $value)
    {
        if ($this->isEmptyValue($value)) {
            return null;
        }

        return $this->serializer->normalize($value, 'json');
    }

    public function validate(FieldDefinition $fieldDefinition, SPIValue $value)
    {
        return $this->mapConstraintViolationList(
            $this->validator->validate($value)
        );
    }

    public function validateFieldSettings($fieldSettings)
    {
        if (empty($fieldSettings)) {
            return [
                new ValidationError(
                    "FieldType '%fieldType%' does not accept settings",
                    null,
                    [
                        'fieldType' => $this->getFieldTypeIdentifier(),
                    ],
                    'fieldType'
                ),
            ];
        }

        return $this->mapConstraintViolationList(
            $this->validator->validate($fieldSettings, $this->getFieldSettingsConstraints())
        );
    }

    protected function getFieldSettingsConstraints(): ?Assert\Collection
    {
        return null;
    }
    
    protected function mapConstraintViolationList(ConstraintViolationListInterface $constraintViolationList): array
    {
        $errors = [];

        /** @var \Symfony\Component\Validator\ConstraintViolationInterface $constraintViolation */
        foreach ($constraintViolationList as $constraintViolation) {
            $errors[] = new ValidationError(
                $constraintViolation->getMessageTemplate(),
                null,
                $constraintViolation->getParameters(),
                $constraintViolation->getPropertyPath()
            );
        }

        return $errors;
    }

    protected function getValueClass(): string
    {
        $typeFQN  = get_called_class();
        $valueFQN = substr_replace($typeFQN, 'Value', strrpos($typeFQN, '\\') + 1);

        return $valueFQN;
    }

    protected function createValueFromInput($inputValue)
    {
        if (is_string($inputValue)) {
            $inputValue = $this->serializer->deserialize($inputValue, $this->getValueClass(), 'json');
        }

        return $inputValue;
    }

    protected function checkValueStructure(BaseValue $value)
    {
        // Value is self-contained and strong typed
        return;
    }
}
