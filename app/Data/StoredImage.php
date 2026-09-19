<?php

namespace App\Data;

use App\Enums\StorageDriver;

readonly class StoredImage
{
    public function __construct(
        public string $key,
        public string $contentType,
        public int $size,
        public StorageDriver $storageDriver
    ) {}
}
