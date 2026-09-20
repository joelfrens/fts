<?php

namespace App\Services;

use App\Contracts\ImageStorageInterface;
use App\Data\ImageProcessingOptions;
use App\Models\Image;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

final class ImageUploadService
{
    public function __construct(
        private ImageProcessor $imageProcessor,
        private ImageStorageInterface $imageStorage,
    ) {}

    public function upload(UploadedFile $file): Image
    {
        $processedImage = $this->imageProcessor->process(
            $file,
            new ImageProcessingOptions(
                maxWidth: 1024,
                maxHeight: 1024,
            )
        );

        $extension = match ($processedImage->contentType) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
        };

        $storageKey = sprintf(
            'images/%s.%s',
            Str::uuid(),
            $extension,
        );

        $storedImage = $this->imageStorage->store(
            contents: $processedImage->contents,
            storageKey: $storageKey,
            contentType: $processedImage->contentType,
        );

        return Image::create([
            'storage_key' => $storedImage->storageKey,
            'original_filename' => $file->getClientOriginalName(),
            'storage_driver' => $storedImage->storageDriver,
            'content_type' => $storedImage->contentType,
            'file_size' => $storedImage->size,
            'width' => $processedImage->width,
            'height' => $processedImage->height,
        ]);
    }
}