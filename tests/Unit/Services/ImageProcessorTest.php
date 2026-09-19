<?php

namespace Tests\Unit\Services;

use App\Data\ImageProcessingOptions;
use App\Exceptions\InvalidImageException;
use App\Services\ImageProcessor;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

final class ImageProcessorTest extends TestCase
{
    private ImageProcessor $processor;
    private ImageProcessingOptions $options;

    protected function setUp(): void
    {
        parent::setUp();

        $this->processor = new ImageProcessor();

        $this->options = new ImageProcessingOptions(
            maxWidth: 1024,
            maxHeight: 1024,
        );
    }

    public function test_it_does_not_resize_small_image(): void
    {
        $file = UploadedFile::fake()->image(
            'photo.jpg',
            800,
            600
        );

        $result = $this->processor->process(
            $file,
            $this->options
        );

        $this->assertSame(800, $result->width);
        $this->assertSame(600, $result->height);
        $this->assertSame(
            'image/jpeg',
            $result->contentType
        );
    }

    public function test_it_resizes_large_landscape_image(): void
    {
        $file = UploadedFile::fake()->image(
            'photo.jpg',
            2000,
            1000
        );

        $result = $this->processor->process(
            $file,
            $this->options
        );

        $this->assertSame(1024, $result->width);
        $this->assertSame(512, $result->height);
    }

    public function test_it_resizes_large_portrait_image(): void
    {
        $file = UploadedFile::fake()->image(
            'photo.jpg',
            1000,
            2000
        );

        $result = $this->processor->process(
            $file,
            $this->options
        );

        $this->assertSame(512, $result->width);
        $this->assertSame(1024, $result->height);
    }

    public function test_it_rejects_invalid_image(): void
    {
        $file = UploadedFile::fake()->createWithContent(
            'fake.jpg',
            'not really an image'
        );

        $this->expectException(
            InvalidImageException::class
        );

        $this->processor->process(
            $file,
            $this->options
        );
    }
}