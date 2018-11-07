<?php

/**
 * File containing the eZ\Publish\Core\FieldType\RichText\Type class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\RichText;

use eZ\Publish\Core\FieldType\FieldType;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;
use eZ\Publish\Core\FieldType\ValidationError;
use eZ\Publish\SPI\FieldType\Value as SPIValue;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\Core\FieldType\Value as BaseValue;
use eZ\Publish\API\Repository\Values\Content\Relation;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use DOMDocument;
use RuntimeException;

/**
 * RichText field type.
 */
class Type extends FieldType
{
    /**
     * @var \eZ\Publish\Core\FieldType\RichText\InputHandler
     */
    private $inputHandler;

    /**
     * @var \eZ\Publish\Core\FieldType\RichText\ValidatorInterface
     */
    private $validator;

    /**
     * @var \eZ\Publish\Core\FieldType\RichText\RelationProcessorInterface
     */
    private $relationProcessor;

    /**
     * @param \eZ\Publish\Core\FieldType\RichText\InputHandler $inputHandler
     * @param \eZ\Publish\Core\FieldType\RichText\ValidatorInterface
     * @param \eZ\Publish\Core\FieldType\RichText\RelationProcessorInterface
     */
    public function __construct(
        InputHandler $inputHandler,
        ValidatorInterface $internalValidator,
        RelationProcessorInterface $relationProcessor
    ) {
        $this->inputHandler = $inputHandler;
        $this->validator = $internalValidator;
        $this->relationProcessor = $relationProcessor;
    }

    /**
     * Returns the field type identifier for this field type.
     *
     * @return string
     */
    public function getFieldTypeIdentifier()
    {
        return 'ezrichtext';
    }

    /**
     * Returns the name of the given field value.
     *
     * It will be used to generate content name and url alias if current field is designated
     * to be used in the content name/urlAlias pattern.
     *
     * @param \eZ\Publish\Core\FieldType\RichText\Value $value
     *
     * @return string
     */
    public function getName(SPIValue $value)
    {
        $result = null;
        if ($section = $value->xml->documentElement->firstChild) {
            $textDom = $section->firstChild;

            if ($textDom && $textDom->hasChildNodes()) {
                $result = $textDom->firstChild->textContent;
            } elseif ($textDom) {
                $result = $textDom->textContent;
            }
        }

        if ($result === null) {
            $result = $value->xml->documentElement->textContent;
        }

        return trim(preg_replace(array('/\n/', '/\s\s+/'), ' ', $result));
    }

    /**
     * Returns the fallback default value of field type when no such default
     * value is provided in the field definition in content types.
     *
     * @return \eZ\Publish\Core\FieldType\RichText\Value
     */
    public function getEmptyValue()
    {
        return new Value();
    }

    /**
     * Returns if the given $value is considered empty by the field type.
     *
     * @param \eZ\Publish\Core\FieldType\RichText\Value $value
     *
     * @return bool
     */
    public function isEmptyValue(SPIValue $value)
    {
        if ($value->xml === null) {
            return true;
        }

        return !$value->xml->documentElement->hasChildNodes();
    }

    /**
     * Inspects given $inputValue and potentially converts it into a dedicated value object.
     *
     * @param \eZ\Publish\Core\FieldType\RichText\Value|\DOMDocument|string $inputValue
     *
     * @return \eZ\Publish\Core\FieldType\RichText\Value The potentially converted and structurally plausible value.
     */
    protected function createValueFromInput($inputValue)
    {
        if (is_string($inputValue)) {
            $inputValue = $this->inputHandler->fromString($inputValue);
        }

        if ($inputValue instanceof DOMDocument) {
            $inputValue = new Value($this->inputHandler->fromDocument($inputValue));
        }

        return $inputValue;
    }

    /**
     * Creates \DOMDocument from given $xmlString.
     *
     * @deprecated since 7.4. Use \eZ\Publish\Core\FieldType\RichText\DOMDocumentFactory::loadXMLString instead.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     *
     * @param $xmlString
     *
     * @return \DOMDocument
     */
    protected function loadXMLString($xmlString)
    {
        $document = new DOMDocument();

        libxml_use_internal_errors(true);
        libxml_clear_errors();

        // Options:
        // - substitute entities
        // - disable network access
        // - relax parser limits for document size/complexity
        $success = $document->loadXML($xmlString, LIBXML_NOENT | LIBXML_NONET | LIBXML_PARSEHUGE);

        if (!$success) {
            $messages = array();

            foreach (libxml_get_errors() as $error) {
                $messages[] = trim($error->message);
            }

            throw new InvalidArgumentException(
                '$inputValue',
                'Could not create XML document: ' . implode("\n", $messages)
            );
        }

        return $document;
    }

    /**
     * Throws an exception if value structure is not of expected format.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the value does not match the expected structure.
     *
     * @param \eZ\Publish\Core\FieldType\RichText\Value $value
     */
    protected function checkValueStructure(BaseValue $value)
    {
        if (!$value->xml instanceof DOMDocument) {
            throw new InvalidArgumentType(
                '$value->xml',
                'DOMDocument',
                $value
            );
        }
    }

    /**
     * Validates a field based on the validators in the field definition.
     *
     * This is a base implementation, returning an empty array() that indicates
     * that no validation errors occurred. Overwrite in derived types, if
     * validation is supported.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition $fieldDefinition The field definition of the field
     * @param \eZ\Publish\Core\FieldType\RichText\Value $value The field value for which an action is performed
     *
     * @return \eZ\Publish\SPI\FieldType\ValidationError[]
     */
    public function validate(FieldDefinition $fieldDefinition, SPIValue $value)
    {
        return array_map(function ($error) {
            return new ValidationError($error);
        }, $this->validator->validateDocument($value->xml));
    }

    /**
     * Returns sortKey information.
     *
     * @see \eZ\Publish\Core\FieldType
     *
     * @param \eZ\Publish\Core\FieldType\RichText\Value $value
     *
     * @return array|bool
     */
    protected function getSortInfo(BaseValue $value)
    {
        return false;
    }

    /**
     * Converts an $hash to the Value defined by the field type.
     * $hash accepts the following keys:
     *  - xml (XML string which complies internal format).
     *
     * @param mixed $hash
     *
     * @return \eZ\Publish\Core\FieldType\RichText\Value $value
     */
    public function fromHash($hash)
    {
        if (!isset($hash['xml'])) {
            throw new RuntimeException("'xml' index is missing in hash.");
        }

        return $this->acceptValue($hash['xml']);
    }

    /**
     * Converts a $Value to a hash.
     *
     * @param \eZ\Publish\Core\FieldType\RichText\Value $value
     *
     * @return mixed
     */
    public function toHash(SPIValue $value)
    {
        return array('xml' => (string)$value);
    }

    /**
     * Creates a new Value object from persistence data.
     * $fieldValue->data is supposed to be a string.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\FieldValue $fieldValue
     *
     * @return \eZ\Publish\Core\FieldType\RichText\Value
     */
    public function fromPersistenceValue(FieldValue $fieldValue)
    {
        return new Value($fieldValue->data);
    }

    /**
     * @param \eZ\Publish\Core\FieldType\RichText\Value $value
     *
     * @return \eZ\Publish\SPI\Persistence\Content\FieldValue
     */
    public function toPersistenceValue(SPIValue $value)
    {
        return new FieldValue(
            array(
                'data' => $value->xml->saveXML(),
                'externalData' => null,
                'sortKey' => $this->getSortInfo($value),
            )
        );
    }

    /**
     * Returns whether the field type is searchable.
     *
     * @return bool
     */
    public function isSearchable()
    {
        return true;
    }

    /**
     * Returns relation data extracted from value.
     *
     * Not intended for \eZ\Publish\API\Repository\Values\Content\Relation::COMMON type relations,
     * there is a service API for handling those.
     *
     * @param \eZ\Publish\Core\FieldType\RichText\Value $fieldValue
     *
     * @return array Hash with relation type as key and array of destination content ids as value.
     *
     * Example:
     * <code>
     *  array(
     *      \eZ\Publish\API\Repository\Values\Content\Relation::LINK => array(
     *          "contentIds" => array( 12, 13, 14 ),
     *          "locationIds" => array( 24 )
     *      ),
     *      \eZ\Publish\API\Repository\Values\Content\Relation::EMBED => array(
     *          "contentIds" => array( 12 ),
     *          "locationIds" => array( 24, 45 )
     *      ),
     *      \eZ\Publish\API\Repository\Values\Content\Relation::FIELD => array( 12 )
     *  )
     * </code>
     */
    public function getRelations(SPIValue $value)
    {
        $relations = array();

        /** @var \eZ\Publish\Core\FieldType\RichText\Value $value */
        if ($value->xml instanceof DOMDocument) {
            $relations = $this->relationProcessor->getRelations($value->xml);
        }

        return $relations;
    }

    /**
     * @deprecated since 7.4. This logic has been moved to \eZ\Publish\Core\FieldType\RichText\RelationProcessor
     */
    protected function getRelatedObjectIds(Value $fieldValue, $relationType)
    {
        if ($relationType === Relation::EMBED) {
            $tagNames = ['ezembedinline', 'ezembed'];
        } else {
            $tagNames = ['link', 'ezlink'];
        }

        $contentIds = array();
        $locationIds = array();
        $xpath = new \DOMXPath($fieldValue->xml);
        $xpath->registerNamespace('docbook', 'http://docbook.org/ns/docbook');

        foreach ($tagNames as $tagName) {
            $xpathExpression = "//docbook:{$tagName}[starts-with( @xlink:href, 'ezcontent://' ) or starts-with( @xlink:href, 'ezlocation://' )]";
            /** @var \DOMElement $element */
            foreach ($xpath->query($xpathExpression) as $element) {
                preg_match('~^(.+)://([^#]*)?(#.*|\\s*)?$~', $element->getAttribute('xlink:href'), $matches);
                list(, $scheme, $id) = $matches;

                if (empty($id)) {
                    continue;
                }

                if ($scheme === 'ezcontent') {
                    $contentIds[] = $id;
                } elseif ($scheme === 'ezlocation') {
                    $locationIds[] = $id;
                }
            }
        }

        return array(
            'locationIds' => array_unique($locationIds),
            'contentIds' => array_unique($contentIds),
        );
    }
}
