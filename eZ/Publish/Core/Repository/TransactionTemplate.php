<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository;

use Doctrine\DBAL\Exception\RetryableException;
use Exception;
use eZ\Publish\API\Repository\Repository as RepositoryInterface;

/**
 * @internal
 */
class TransactionTemplate
{
    /** @var RepositoryInterface */
    private $repository;

    /** @var int */
    private $maxRetries;

    public function __construct(RepositoryInterface $repository, $maxRetries = 3)
    {
        $this->repository = $repository;
        $this->maxRetries = $maxRetries;
    }

    public function execute(callable $callback)
    {
        $retry = 0;
        while ($retry < $this->maxRetries) {
            $this->repository->beginTransaction();
            try {
                $callback();
                $this->repository->commit();
                break;
            } catch (Exception $e) {
                $this->repository->rollback();
                $retry++;

                if ($retry < $this->maxRetries || !$e->getPrevious() instanceof RetryableException) {
                    throw $e;
                }
            }
        }
    }
}
