<?php

/**
 * File containing the User Value class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\User;

use DateInterval;
use DateTime;
use DateTimeInterface;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\Core\FieldType\Value as BaseValue;

/**
 * Value for User field type.
 */
class Value extends BaseValue
{
    /**
     * Has stored login.
     *
     * @var bool
     */
    public $hasStoredLogin;

    /**
     * Contentobject id.
     *
     * @var mixed
     */
    public $contentId;

    /**
     * Login.
     *
     * @var string
     */
    public $login;

    /**
     * Email.
     *
     * @var string
     */
    public $email;

    /**
     * Password hash.
     *
     * @var string
     */
    public $passwordHash;

    /**
     * Password hash type.
     *
     * @var mixed
     */
    public $passwordHashType;

    /**
     * @var \DateTimeImmutable|null
     */
    public $passwordUpdateAt;

    /**
     * Is enabled.
     *
     * @var bool
     */
    public $enabled;

    /**
     * Max login.
     *
     * @var int
     */
    public $maxLogin;

    public function __construct(array $properties = [])
    {
        $passwordUpdateAt = $properties['passwordUpdateAt'] ?? null;
        if ($passwordUpdateAt !== null) {
            $dateTime = new DateTime();
            $dateTime->setTimestamp($passwordUpdateAt);

            $this->passwordUpdateAt = $dateTime;

            unset($properties['passwordUpdateAt']);
        }

        parent::__construct($properties);
    }

    public function hasPasswordExpiresDate(FieldDefinition $fieldDefinition): bool
    {
        if ($this->passwordUpdateAt instanceof DateTimeInterface) {
            return $fieldDefinition->fieldSettings['PasswordExpireAfter'] > 0;
        }

        return false;
    }

    public function getPasswordExpiresAt(FieldDefinition $fieldDefinition): ?DateTimeInterface
    {
        if (!$this->hasPasswordExpiresDate($fieldDefinition)) {
            return null;
        }

        return $this->passwordUpdateAt->add(
            new DateInterval(sprintf("P%dD", (int)$fieldDefinition->fieldSettings['PasswordExpireAfter']))
        );
    }

    public function isPasswordExpired(FieldDefinition $fieldDefinition): bool
    {
        $expiresAt = $this->getPasswordExpiresAt($fieldDefinition);
        if ($expiresAt !== null) {
            return $expiresAt < (new DateTime());
        }

        return false;
    }

    /**
     * @see \eZ\Publish\Core\FieldType\Value
     */
    public function __toString()
    {
        return (string)$this->login;
    }
}
