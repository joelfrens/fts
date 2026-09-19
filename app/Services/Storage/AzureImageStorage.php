<?php

namespace App\Services\Storage;

use App\Contracts\ImageStorageInterface;
use App\Data\StoredImage;
use App\Enums\StorageDriver;

class AzureImageStorage implements ImageStorageInterface
{
    /**
     * Stores the image on Azure.
     * 
     * @param string $contents The contents of the image.
     * @param string $storageKey The key of the image.
     * @param string $contentType The content type of the image.
     * @return StoredImage The stored image.
     */
    public function store(string $contents, string $storageKey, string $contentType): StoredImage
    {
        // TODO: Implement store method

        return new StoredImage(
            storageKey: $storageKey,
            contentType: $contentType,
            size: strlen($contents),
            storageDriver: StorageDriver::Azure,
        );
    }
}   