<?php

namespace App\Services;

use App\Data\ImageProcessingOptions;
use App\Data\ProcessedImage;
use App\Exceptions\InvalidImageException;
use Illuminate\Http\UploadedFile;
use RuntimeException;

final class ImageProcessor
{
    public function process(
        UploadedFile $file,
        ImageProcessingOptions $options,
    ): ProcessedImage {
        $contents = file_get_contents($file->getRealPath());

        if ($contents === false) {
            throw new InvalidImageException(
                'Unable to read uploaded image.'
            );
        }

        $imageInfo = getimagesizefromstring($contents);

        if ($imageInfo === false) {
            throw new InvalidImageException(
                'Uploaded file is not a valid image.'
            );
        }

        [$width, $height, $imageType] = $imageInfo;

        $contentType = match ($imageType) {
            IMAGETYPE_JPEG => 'image/jpeg',
            IMAGETYPE_PNG => 'image/png',
            default => throw new InvalidImageException(
                'Only JPEG and PNG images are supported.'
            ),
        };

        // Already fits. Don't resize or re-encode it.
        if (
            $width <= $options->maxWidth &&
            $height <= $options->maxHeight
        ) {
            return new ProcessedImage(
                contents: $contents,
                contentType: $contentType,
                width: $width,
                height: $height,
            );
        }

        $source = imagecreatefromstring($contents);

        if ($source === false) {
            throw new InvalidImageException(
                'Unable to decode uploaded image.'
            );
        }

        $scale = min(
            $options->maxWidth / $width,
            $options->maxHeight / $height,
        );

        $newWidth = max(
            1,
            (int) round($width * $scale)
        );

        $newHeight = max(
            1,
            (int) round($height * $scale)
        );

        $resized = imagecreatetruecolor(
            $newWidth,
            $newHeight
        );

        if ($resized === false) {
            imagedestroy($source);

            throw new RuntimeException(
                'Unable to create resized image.'
            );
        }

        // Keep PNG transparency.
        if ($imageType === IMAGETYPE_PNG) {
            imagealphablending($resized, false);
            imagesavealpha($resized, true);

            $transparent = imagecolorallocatealpha(
                $resized,
                0,
                0,
                0,
                127
            );

            imagefill(
                $resized,
                0,
                0,
                $transparent
            );
        }

        $success = imagecopyresampled(
            $resized,
            $source,
            0,
            0,
            0,
            0,
            $newWidth,
            $newHeight,
            $width,
            $height,
        );

        if (! $success) {
            imagedestroy($source);
            imagedestroy($resized);

            throw new RuntimeException(
                'Unable to resize image.'
            );
        }

        ob_start();

        $encoded = match ($imageType) {
            IMAGETYPE_JPEG => imagejpeg(
                $resized,
                null
            ),

            IMAGETYPE_PNG => imagepng(
                $resized
            ),
        };

        $processedContents = ob_get_clean();

        imagedestroy($source);
        imagedestroy($resized);

        if ($encoded === false || $processedContents === false) {
            throw new RuntimeException(
                'Unable to encode resized image.'
            );
        }

        return new ProcessedImage(
            contents: $processedContents,
            contentType: $contentType,
            width: $newWidth,
            height: $newHeight,
        );
    }
}