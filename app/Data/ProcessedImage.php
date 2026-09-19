<?php

namespace App\Data;

final readonly class ProcessedImage
{
    public function __construct(
        public string $contents,
        public string $contentType,
        public int $width,
        public int $height,
    ) {}
}