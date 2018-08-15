<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\SPI\Persistence\ContentReview;

interface Handler
{
    public function create(CreateStruct $createStruct): ContentReview;

    public function update(UpdateStruct $updateStruct): void;

    public function createComment(CommentCreateStruct $createStruct): Comment;

    /**
     * @param int $contentId
     * @param int $versionNo
     * @return ContentReview[]
     */
    public function loadByVersionInfo(int $contentId, int $versionNo): array;
}
