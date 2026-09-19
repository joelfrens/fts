<?php

namespace App\Data;

use App\Enums\StorageDriver;

readonly class StoredImage
{
    public function __construct(
        public string $storageKey,
        public string $contentType,
        public int $size,
        public StorageDriver $storageDriver
    ) {}
}
