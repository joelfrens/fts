<?php

namespace App\Services\Storage;

use App\Contracts\ImageStorageInterface;
use App\Data\StoredImage;
use App\Enums\StorageDriver;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Illuminate\Http\Client\ConnectionException;


final class AzureImageStorage implements ImageStorageInterface
{
    public function store(
        string $contents,
        string $storageKey,
        string $contentType,
    ): StoredImage {
        $sasUrl = config('image-storage.azure.blob_sas_url');

        if (! is_string($sasUrl) || $sasUrl === '') {
            throw new RuntimeException(
                'Azure Blob SAS URL is not configured.'
            );
        }

        $blobUrl = $this->buildBlobUrl(
            sasUrl: $sasUrl,
            storageKey: $storageKey,
        );

        try {
            $response = Http::connectTimeout(3)
                ->timeout(10)
                ->withHeaders([
                    'x-ms-blob-type' => 'BlockBlob',
                ])
                ->withBody($contents, $contentType)
                ->put($blobUrl);
        } catch (ConnectionException $exception) {
            throw new RuntimeException(
                'Unable to connect to Azure Blob Storage.',
                previous: $exception,
            );
        }

        if (! $response->successful()) {
            throw new RuntimeException(
                sprintf(
                    'Azure Blob upload failed with status %d.',
                    $response->status(),
                )
            );
        }

        return new StoredImage(
            storageKey: $storageKey,
            contentType: $contentType,
            size: strlen($contents),
            storageDriver: StorageDriver::Azure,
        );
    }

    public function url(string $storageKey): string
    {
        $publicUrl = config(
            'image-storage.azure.public_url'
        );

        if (! is_string($publicUrl) || $publicUrl === '') {
            throw new RuntimeException(
                'Azure Blob public URL is not configured.'
            );
        }

        return sprintf(
            '%s/%s',
            rtrim($publicUrl, '/'),
            ltrim($storageKey, '/'),
        );
    }

    private function buildBlobUrl(
        string $sasUrl,
        string $storageKey,
    ): string {
        $parts = parse_url($sasUrl);

        $container = config(
            'image-storage.azure.blob_container'
        );

        if (
            $parts === false ||
            ! isset(
                $parts['scheme'],
                $parts['host'],
                $parts['query'],
            )
        ) {
            throw new RuntimeException(
                'Invalid Azure Blob SAS URL.'
            );
        }

        if (
            ! is_string($container) ||
            $container === ''
        ) {
            throw new RuntimeException(
                'Azure Blob container is not configured.'
            );
        }

        return sprintf(
            '%s://%s/%s/%s?%s',
            $parts['scheme'],
            $parts['host'],
            trim($container, '/'),
            ltrim($storageKey, '/'),
            $parts['query'],
        );
    }
}