<?php

namespace Tests\Unit;

use App\Enums\StorageDriver;
use App\Services\Storage\LocalImageStorage;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LocalImageStorageTest extends TestCase
{
    public function test_stores_the_image_on_the_public_disk(): void
    {
        Storage::fake('public');

        $contents = 'test';
        $storageKey = 'test.jpg';
        $contentType = 'image/jpeg';

        $storedImage = (new LocalImageStorage)->store($contents, $storageKey, $contentType);

        $this->assertSame($storageKey, $storedImage->storageKey);
        $this->assertSame($contentType, $storedImage->contentType);
        $this->assertSame(4, $storedImage->size);
        $this->assertSame(StorageDriver::Local, $storedImage->storageDriver);
        Storage::disk('public')->assertExists($storageKey, $contents);
    }
}
