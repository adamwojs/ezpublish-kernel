<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\FieldType\GenericEntity;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\Core\FieldType\FieldType;
use eZ\Publish\Core\FieldType\ValidationError;
use eZ\Publish\Core\FieldType\Value as BaseValue;
use eZ\Publish\SPI\FieldType\Value as SPIValue;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\SPI\Persistence\Content\FieldValue as PersistenceValue;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class Type extends FieldType
{
    /** @var \Doctrine\ORM\EntityManagerInterface */
    protected $em;

    /** @var \Symfony\Component\Validator\Validator\ValidatorInterface */
    protected $validator;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @param \Symfony\Component\Validator\Validator\ValidatorInterface $validator
     */
    public function __construct(EntityManagerInterface $entityManager, ValidatorInterface $validator)
    {
        $this->em = $entityManager;
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
            return $this->getValueRepository()->find($hash);
        }

        return $this->getEmptyValue();
    }

    public function toHash(SPIValue $value)
    {
        if ($this->isEmptyValue($value)) {
            return null;
        }

        return [
            'id' => $this->getValueMetadata()->getIdentifierValues($value),
            'class' => $this->getValueClass(),
        ];
    }

    public function validate(FieldDefinition $fieldDefinition, SPIValue $value)
    {
        $validationErrors = [];

        $errors = $this->validator->validate($value);
        /** @var \Symfony\Component\Validator\ConstraintViolationInterface $error */
        foreach ($errors as $error) {
            $validationErrors[] = new ValidationError(
                $error->getMessageTemplate(),
                null,
                $error->getParameters(),
                $error->getPropertyPath()
            );
        }

        return $validationErrors;
    }

    public function toPersistenceValue(SPIValue $value)
    {
        if ($value === null) {
            return new FieldValue([
                'data' => null,
                'externalData' => null,
                'sortKey' => null,
            ]);
        }

        return new FieldValue([
            'data' => null,
            'externalData' => $value,
            'sortKey' => $this->getSortInfo($value),
        ]);
    }

    public function fromPersistenceValue(PersistenceValue $fieldValue)
    {
        if ($fieldValue->externalData === null) {
            return $this->getEmptyValue();
        }

        return $fieldValue->externalData;
    }

    abstract protected function getValueClass(): string;

    protected function createValueFromInput($inputValue)
    {
        // TODO: Support for composite keys
        if (is_int($inputValue) || is_string($inputValue)) {
            $inputValue = $this->getValueRepository()->find($inputValue);
        }

        return $inputValue;
    }

    protected function checkValueStructure(BaseValue $value)
    {
        // Value is self-contained and strong typed
        return;
    }

    protected static function checkValueType($value)
    {
        // We are not able to do type check check in static scope
        return;
    }

    private function getValueMetadata(): ClassMetadata
    {
        return $this->em->getClassMetadata($this->getValueClass());
    }

    private function getValueRepository(): ObjectRepository
    {
        return $this->em->getRepository($this->getValueClass());
    }
}
