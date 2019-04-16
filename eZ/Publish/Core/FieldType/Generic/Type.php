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
use eZ\Publish\SPI\FieldType\Nameable;
use eZ\Publish\SPI\FieldType\Value as SPIValue;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class Type extends FieldType implements Nameable
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

    /**
     * @see https://symfony.com/doc/current/validation/raw_values.html
     *
     * @return null|\Symfony\Component\Validator\Constraints\Collection
     */
    protected function getFieldSettingsConstraints(): ?Assert\Collection
    {
        return null;
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition $fieldDefinition
     *
     * @return null|\Symfony\Component\Validator\Constraints\Collection
     */
    protected function getFieldValueConstraints(FieldDefinition $fieldDefinition): ?Assert\Collection
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

    /**
     * Returns FQN of class representing Field Type Value.
     *
     * @return string
     */
    protected function getValueClass(): string
    {
        $typeFQN  = get_called_class();
        $valueFQN = substr_replace($typeFQN, 'Value', strrpos($typeFQN, '\\') + 1);

        return $valueFQN;
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
//        $constraints = $this->getFieldValueConstraints($fieldDefinition);
//        if ($constraints === null) {
//            $constraints = $this->validator->getMetadataFor($value)->getConstraints();
//
//            foreach ($constraints as $constraint) {
//                $options = get_object_vars($constraint);
//
//                $settings = $fieldDefinition->fieldSettings;
//                foreach ($options as $name => $value) {
//                    if (!is_string($value)) {
//                        continue;
//                    }
//
//                    $matches = [];
//                    if (preg_match('/^{{ ([a-zA-Z][a-zA-Z0-9]*) }}$/', $value, $matches)) {
//                        $constraint->{$name} = $settings[$matches[1]];
//                    }
//                }
//
//                // Set up field definition as payload for constraint
//                // $constraint->payload = $fieldDefinition;
//            }
//        }

        return $this->mapConstraintViolationList(
            $this->validator->validate($value, $this->getFieldValueConstraints($fieldDefinition))
        );
    }

    public function validateFieldSettings($fieldSettings)
    {
        if (empty($this->settingsSchema) && !empty($fieldSettings)) {
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

        if (!is_array($fieldSettings)) {
            return [];
        }

        return $this->mapConstraintViolationList(
            $this->validator->validate($fieldSettings, $this->getFieldSettingsConstraints())
        );
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

    public function getFieldName(SPIValue $value, FieldDefinition $fieldDefinition, $languageCode)
    {
        return (string)$value;
    }
}
