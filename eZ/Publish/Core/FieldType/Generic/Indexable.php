<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\FieldType\Generic;

use Doctrine\Common\Annotations\AnnotationReader;
use eZ\Publish\API\Repository\FieldTypeService;
use eZ\Publish\Core\FieldType\ISBN\SearchField;
use eZ\Publish\SPI\FieldType\Indexable as IndexableInterface;
use \eZ\Publish\Core\FieldType\Generic\Indexable\Indexable as IndexableAnnotation;
use \eZ\Publish\Core\FieldType\Generic\Indexable\Field as FieldAnnotation;
use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition;
use eZ\Publish\SPI\Search\FieldType\BooleanField;
use eZ\Publish\SPI\Search\FieldType\DateField;
use eZ\Publish\SPI\Search\FieldType\FloatField;
use eZ\Publish\SPI\Search\FieldType\FullTextField;
use eZ\Publish\SPI\Search\FieldType\GeoLocationField;
use eZ\Publish\SPI\Search\FieldType\IdentifierField;
use eZ\Publish\SPI\Search\FieldType\IntegerField;
use eZ\Publish\SPI\Search\FieldType\MultipleBooleanField;
use eZ\Publish\SPI\Search\FieldType\MultipleIdentifierField;
use eZ\Publish\SPI\Search\FieldType\MultipleIntegerField;
use eZ\Publish\SPI\Search\FieldType\MultipleStringField;
use eZ\Publish\SPI\Search\FieldType\PriceField;
use eZ\Publish\SPI\Search\FieldType\TextField;
use eZ\Publish\SPI\Search\FieldType as SearchFieldType;
use ReflectionClass;
use eZ\Publish\SPI\Search;

class Indexable implements IndexableInterface
{
    private const TYPE_TO_CLASS_MAP = [
        "ez_boolean" => BooleanField::class,
        "ez_date" => DateField::class,
        "ez_float" => FloatField::class,
        "ez_fulltext" => FullTextField::class,
        "ez_geolocation" => GeoLocationField::class,
        "ez_id" => IdentifierField::class,
        "ez_integer" => IntegerField::class,
        "ez_mboolean" => MultipleBooleanField::class,
        "ez_mid" => MultipleIdentifierField::class,
        "ez_minteger" => MultipleIntegerField::class,
        "ez_mstring" => MultipleStringField::class,
        "ez_currency" => PriceField::class,
        "ez_string" => SearchField::class,
        "ez_text" => TextField::class,
    ];

    /** @var \Doctrine\Common\Annotations\AnnotationReader */
    private $reader;

    /** @var \eZ\Publish\API\Repository\FieldTypeService */
    private $fieldTypeService;

    /** @var string */
    private $valueClass;

    public function __construct(AnnotationReader $reader, FieldTypeService $fieldTypeService, string $valueClass)
    {
        $this->reader = $reader;
        $this->fieldTypeService = $fieldTypeService;
        $this->valueClass = $valueClass;
    }

    public function getIndexData(Field $field, FieldDefinition $fieldDefinition)
    {
        $class = new ReflectionClass($this->valueClass);

        if (!$this->isIndexable($class)) {
            return [];
        }

        // Konieczne żeby można użyć ReflectionProperty::getValue()
        $value = $this->fieldTypeService->getFieldType($fieldDefinition->fieldType)->fromHash($field->value);

        $searchFields = [];
        foreach ($class->getProperties() as $property) {
            $annotation = $this->reader->getPropertyAnnotation($property, FieldAnnotation::class);
            if (!($annotation instanceof FieldAnnotation)) {
                continue;
            }

            $property->setAccessible(true);

            $searchFields[] = new Search\Field(
                $annotation->name ?: $property->name,
                $property->getValue($value),
                $this->createSearchFieldType($annotation)
            );
        }

        dump($searchFields);

        return $searchFields;
    }

    public function getIndexDefinition()
    {
        $class = new ReflectionClass($this->valueClass);

        if (!$this->isIndexable($class)) {
            return [];
        }

        $index = [];
        foreach ($class->getProperties() as $property) {
            $annotation = $this->reader->getPropertyAnnotation($property, FieldAnnotation::class);
            if (!($annotation instanceof FieldAnnotation)) {
                continue;
            }

            $searchFieldName = $annotation->name ?: $property->name;
            $searchFieldType = $this->createSearchFieldType($annotation);

            $index[$searchFieldName] = $searchFieldType;
        }

        return $index;
    }

    public function getDefaultMatchField()
    {
        $class = new ReflectionClass($this->valueClass);

        if (!$this->isIndexable($class)) {
            return [];
        }

        foreach ($class->getProperties() as $property) {
            $annotation = $this->reader->getPropertyAnnotation($property, FieldAnnotation::class);
            if (!($annotation instanceof FieldAnnotation)) {
                continue;
            }

            if ($annotation->isDefaultMatch) {
                return $annotation->name ?: $property->name;
            }
        }

        throw new \RuntimeException("No default match field found.");
    }

    public function getDefaultSortField()
    {
        $class = new ReflectionClass($this->valueClass);

        if (!$this->isIndexable($class)) {
            return [];
        }

        foreach ($class->getProperties() as $property) {
            $annotation = $this->reader->getPropertyAnnotation($property, FieldAnnotation::class);
            if (!($annotation instanceof FieldAnnotation)) {
                continue;
            }

            if ($annotation->isDefaultSortField) {
                return $annotation->name ?: $property->name;
            }
        }

        throw new \RuntimeException("No default match field found.");
    }

    private function isIndexable(ReflectionClass $class): bool
    {
        return $this->reader->getClassAnnotation($class, IndexableAnnotation::class) !== null;
    }

    private function createSearchFieldType(FieldAnnotation $annotation): SearchFieldType
    {
        $class = self::TYPE_TO_CLASS_MAP[$annotation->type];

        return new $class([
            // TODO: Search Field options
        ]);
    }
}
