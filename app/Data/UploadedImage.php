<?php

namespace App\Data;

use App\Models\Image;

final readonly class UploadedImage
{
    public function __construct(
        public Image $image,
        public string $url,
    ) {}
}