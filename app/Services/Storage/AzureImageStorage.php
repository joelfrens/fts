<?php

namespace App\Services\Storage;

use App\Contracts\ImageStorageInterface;
use App\Data\StoredImage;
use App\Enums\StorageDriver;

class AzureImageStorage implements ImageStorageInterface
{
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