<?php

namespace App\Contracts;

use App\Data\StoredImage;

interface ImageStorageInterface
{
    public function store(
        string $contents,
        string $key,
        string $contentType,
    ): StoredImage;
}
