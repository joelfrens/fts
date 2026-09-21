<?php

namespace App\Services\Storage;

use App\Contracts\ImageStorageInterface;
use App\Data\StoredImage;
use App\Enums\StorageDriver;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class LocalImageStorage implements ImageStorageInterface
{
    /**
     * Stores the image on the public disk.
     * 
     * @param string $contents The contents of the image.
     * @param string $storageKey The key of the image.
     * @param string $contentType The content type of the image.
     * @return StoredImage The stored image.
     */
    public function store(string $contents, string $storageKey, string $contentType): StoredImage
    {    
        if (!Storage::disk('public')->put($storageKey, $contents)) {
            throw new RuntimeException('Failed to store image on the public disk.');
        }

        return new StoredImage(
            storageKey: $storageKey,
            contentType: $contentType,
            size: strlen($contents),
            storageDriver: StorageDriver::Local,
        );
    }           

    public function url(string $storageKey): string
    {
        return Storage::disk('public')->url($storageKey);
    }

}