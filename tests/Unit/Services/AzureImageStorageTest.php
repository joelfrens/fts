<?php

namespace Tests\Unit\Services\Storage;

use App\Enums\StorageDriver;
use App\Services\Storage\AzureImageStorage;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;


final class AzureImageStorageTest extends TestCase
{
    public function test_it_stores_image_in_azure(): void
    {
        config()->set([
            'image-storage.azure.blob_sas_url' =>
                'https://account.blob.core.windows.net/?sv=test&sig=test',

            'image-storage.azure.blob_container' =>
                'container',

            'image-storage.azure.public_url' =>
                'https://account.blob.core.windows.net/container',
        ]);

        Http::fake([
            '*' => Http::response('', 201),
        ]);

        $storage = new AzureImageStorage();

        $result = $storage->store(
            contents: 'image-contents',
            storageKey: 'images/test.jpg',
            contentType: 'image/jpeg',
        );

        $this->assertSame(
            'images/test.jpg',
            $result->storageKey
        );

        $this->assertSame(
            'image/jpeg',
            $result->contentType
        );

        $this->assertSame(
            strlen('image-contents'),
            $result->size
        );

        $this->assertSame(
            StorageDriver::Azure,
            $result->storageDriver
        );

        Http::assertSent(function ($request) {
            return $request->method() === 'PUT'
                && $request->url()
                    === 'https://account.blob.core.windows.net/container/images/test.jpg?sv=test&sig=test'
                && $request->hasHeader(
                    'x-ms-blob-type',
                    'BlockBlob'
                )
                && $request->hasHeader(
                    'Content-Type',
                    'image/jpeg'
                )
                && $request->body()
                    === 'image-contents';
        });
    }

    public function test_it_throws_exception_when_azure_fails(): void
    {
        config()->set(
            'image-storage.azure.blob_sas_url',
            'https://account.blob.core.windows.net/container?sv=test&sig=test'
        );

        Http::fake([
            '*' => Http::response('', 500),
        ]);

        $this->expectException(RuntimeException::class);

        $this->expectExceptionMessage(
            'Azure Blob upload failed with status 500.'
        );

        $storage = new AzureImageStorage();

        $storage->store(
            contents: 'image-contents',
            storageKey: 'images/test.jpg',
            contentType: 'image/jpeg',
        );
    }

    public function test_it_throws_exception_when_sas_url_is_missing(): void
    {
        config()->set(
            'image-storage.azure.blob_sas_url',
            null
        );

        $this->expectException(RuntimeException::class);

        $this->expectExceptionMessage(
            'Azure Blob SAS URL is not configured.'
        );

        $storage = new AzureImageStorage();

        $storage->store(
            contents: 'image-contents',
            storageKey: 'images/test.jpg',
            contentType: 'image/jpeg',
        );
    }

    public function test_it_throws_exception_when_azure_cannot_be_reached(): void
    {
        config()->set(
            'image-storage.azure.blob_sas_url',
            'https://account.blob.core.windows.net/container?sv=test&sig=test'
        );

        Http::fake([
            '*' => Http::failedConnection(),
        ]);

        $this->expectException(RuntimeException::class);

        $this->expectExceptionMessage(
            'Unable to connect to Azure Blob Storage.'
        );

        $storage = new AzureImageStorage();

        $storage->store(
            contents: 'image-contents',
            storageKey: 'images/test.jpg',
            contentType: 'image/jpeg',
        );
    }
}