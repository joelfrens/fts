<?php

namespace App\Data;

use InvalidArgumentException;

final readonly class ImageProcessingOptions
{
    /**
     * @param int $maxWidth
     * @param int $maxHeight
     */
    public function __construct(
        public int $maxWidth = 1024,
        public int $maxHeight = 1024
    ) {
        if ($maxWidth <= 0 || $maxHeight <= 0) {
            throw new InvalidArgumentException(
                'Maximum dimensions must be greater than zero.'
            );
        }
    }
}