<?php

namespace App\Services\Storage;

use App\Contracts\ImageStorageInterface;
use App\Data\StoredImage;
use App\Enums\StorageDriver;
use Illuminate\Support\Facades\Storage;

class LocalImageStorage implements ImageStorageInterface
{
    public function store(string $contents, string $key, string $contentType): StoredImage
    {
        Storage::disk('public')->put($key, $contents);
        
        return new StoredImage(
            storageKey: $key,
            contentType: $contentType,
            size: strlen($contents),
            storageDriver: StorageDriver::Local,
        );
    }           

}