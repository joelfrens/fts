<?php

namespace App\Data;

use App\Enums\StorageDriver;

readonly class StoredImage
{
    /**
     * Creates a new stored image.
     * 
     * @param string $storageKey The key of the image in the storage.
     * @param string $contentType The content type of the image.
     * @param int $size The size of the image.
     * @param StorageDriver $storageDriver The driver used to store the image.
     */
    public function __construct(
        public string $storageKey,
        public string $contentType,
        public int $size,
        public StorageDriver $storageDriver
    ) {}
}
