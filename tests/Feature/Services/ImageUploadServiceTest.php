<?php

namespace Tests\Feature\Services;

use App\Contracts\ImageStorageInterface;
use App\Enums\StorageDriver;
use App\Models\Image;
use App\Services\ImageProcessor;
use App\Services\ImageUploadService;
use App\Services\Storage\LocalImageStorage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use App\Data\UploadedImage;

final class ImageUploadServiceTest extends TestCase
{
    use RefreshDatabase;

    private ImageUploadService $service;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->service = new ImageUploadService(
            imageProcessor: new ImageProcessor(),
            imageStorage: new LocalImageStorage(),
        );
    }

    public function test_it_processes_stores_and_records_uploaded_image(): void
    {
        $file = UploadedFile::fake()->image(
            'holiday.jpg',
            2000,
            1000,
        );

        $image = $this->service->upload($file);

        $this->assertInstanceOf(UploadedImage::class, $image);

        $this->assertSame(
            'holiday.jpg',
            $image->image->original_filename
        );

        $this->assertSame(
            'image/jpeg',
            $image->image->content_type
        );

        $this->assertSame(
            StorageDriver::Local,
            $image->image->storage_driver
        );

        $this->assertSame(1024, $image->image->width);
        $this->assertSame(512, $image->image->height);

        Storage::disk('public')->assertExists(
            $image->image->storage_key
        );

        $this->assertDatabaseHas('images', [
            'id' => $image->image->id,
            'original_filename' => 'holiday.jpg',
            'storage_key' => $image->image->storage_key,
            'storage_driver' => StorageDriver::Local->value,
            'content_type' => 'image/jpeg',
            'width' => 1024,
            'height' => 512,
        ]);
    }
}