<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository;

use DateTimeInterface;
use eZ\Publish\API\Repository\Values\User\PasswordValidationContext;
use eZ\Publish\API\Repository\Values\User\User;

interface PasswordService
{
    /**
     * Returns password hash based on user data and site settings.
     *
     * @param string $login User login
     * @param string $password User password
     * @param string $site The name of the site
     * @param int $type Type of password to generate
     *
     * @return string Generated password hash
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the type is not recognized
     */
    public function createPasswordHash(string $login, string $password, ?string $site, int $type): string;

    /**
     * Returns password expiration date for given user.
     *
     * @param \eZ\Publish\API\Repository\Values\User\User $user
     *
     * @return \DateTimeInterface|null
     */
    public function getPasswordExpirationDate(User $user): ?DateTimeInterface;

    /**
     * Returns password expiration warning date for given user.
     *
     * @param \eZ\Publish\API\Repository\Values\User\User $user
     *
     * @return \DateTimeInterface|null
     */
    public function getPasswordExpirationWarningDate(User $user): ?DateTimeInterface;

    /**
     * Returns true if user password expired.
     *
     * @param \eZ\Publish\API\Repository\Values\User\User $user
     *
     * @return bool
     */
    public function isPasswordExpired(User $user): bool;

    /**
     * Validates given password.
     *
     * @param string $password
     * @param \eZ\Publish\API\Repository\Values\User\PasswordValidationContext|null $context
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     *
     * @return \eZ\Publish\SPI\FieldType\ValidationError[]
     */
    public function validatePassword(string $password, ?PasswordValidationContext $context = null): array;

    /**
     * Verifies if the provided login and password are valid.
     *
     * @param string $login User login
     * @param string $password User password
     * @param \eZ\Publish\API\Repository\Values\User\User $user Loaded user handler
     *
     * @return bool return true if the login and password are sucessfully
     * validate and false, if not.
     */
    public function verifyPassword(string $login, string $password, User $user): bool;
}
