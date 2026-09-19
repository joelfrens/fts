<?php

namespace Tests\Unit;

use App\Enums\StorageDriver;
use App\Services\Storage\LocalImageStorage;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Illuminate\Contracts\Filesystem\Filesystem;
use RuntimeException;

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
    
    public function test_it_throws_exception_when_storage_fails(): void
    {
        $disk = $this->mock(Filesystem::class);

        Storage::shouldReceive('disk')
            ->once()
            ->with('public')
            ->andReturn($disk);

        $disk->shouldReceive('put')
            ->once()
            ->with(
                'images/test.jpg',
                'image-contents'
            )
            ->andReturn(false);

        $this->expectException(RuntimeException::class);

        $this->expectExceptionMessage(
            'Failed to store image on the public disk.'
        );

        $storage = new LocalImageStorage();

        $storage->store(
            contents: 'image-contents',
            storageKey: 'images/test.jpg',
            contentType: 'image/jpeg',
        );
    }
}
