<p align="center">
    <img src="public/favicon.svg" width="120" alt="FTS">
</p>

# FTS

Laravel image storage app with pluggable drivers (local and Azure).

## Architecture

![Upload flow from the Frontend UI through the Laravel Images API, then Image Upload Service, Image Processor, and ImageUpload Interface to Azure or local storage, with a database audit record](docs/architecture.png)

## ImageController

`ImageController` is the API layer in the diagram. It accepts an upload, validates it, and hands the file to `ImageUploadService`. That service processes the image, stores it with the configured driver (`FTS_STORAGE_DRIVER`), and writes an audit row to the `images` table.

There is no authentication on this endpoint yet.

### `POST /api/images`

Create a new image. Send `multipart/form-data` with a single file field named `image`.

```bash
curl -X POST http://fts.test/api/images \
  -F "image=@photo.jpg"
```

**Validation** (`StoreImageRequest`):

| Field | Rules |
| --- | --- |
| `image` | required file; `jpg`, `jpeg`, or `png`; max 10 MB |

Invalid requests return Laravel's standard `422` JSON error payload.

**Success** (`201 Created`):

```json
{
  "data": {
    "id": 1,
    "storage_key": "images/9b1deb4d-3b7d-4bad-9bdd-2b0d7b3dcb6d.jpg",
    "content_type": "image/jpeg",
    "width": 1024,
    "height": 768
  }
}
```

Images are resized to a maximum of 1024×1024 before storage. The original filename, driver, and file size are stored on the `images` row but are not returned in this response.

## Technical documentation

### Enum

`App\Enums\StorageDriver` is a string-backed enum. It is the allowed set of storage backends and is persisted on `images.storage_driver`.

| Case | Value |
| --- | --- |
| `StorageDriver::Local` | `local` |
| `StorageDriver::Azure` | `azure` |

`FTS_STORAGE_DRIVER` in `.env` must be one of those values. `AppServiceProvider` uses it to bind the storage implementation. The `Image` model casts `storage_driver` to this enum.

### DTOs

Readonly data objects in `app/Data`. They move image data between processor, storage, and persistence without sharing Eloquent models across those layers.

**`ImageProcessingOptions`** — resize limits for `ImageProcessor`. Defaults are `1024` × `1024`. Construction throws `InvalidArgumentException` if either dimension is `<= 0`.

**`ProcessedImage`** — output of `ImageProcessor`:

| Property | Type | Meaning |
| --- | --- | --- |
| `contents` | `string` | Binary image bytes (original or resized) |
| `contentType` | `string` | `image/jpeg` or `image/png` |
| `width` | `int` | Pixel width after processing |
| `height` | `int` | Pixel height after processing |

**`StoredImage`** — output of any `ImageStorageInterface` implementation:

| Property | Type | Meaning |
| --- | --- | --- |
| `storageKey` | `string` | Path/key in the storage backend |
| `contentType` | `string` | MIME type stored with the file |
| `size` | `int` | Byte length of `contents` |
| `storageDriver` | `StorageDriver` | Backend that wrote the file |

### Interface

`App\Contracts\ImageStorageInterface` is the storage port. Upload code depends on this contract, not on Azure or local disks.

```php
public function store(
    string $contents,
    string $storageKey,
    string $contentType,
): StoredImage;
```

`AppServiceProvider` binds the contract from `config('image-storage.driver')` (`FTS_STORAGE_DRIVER`):

| Driver | Implementation |
| --- | --- |
| `local` | `App\Services\Storage\LocalImageStorage` |
| `azure` | `App\Services\Storage\AzureImageStorage` |
| anything else | `RuntimeException` |

To add a backend, implement the interface and add a `match` arm in `AppServiceProvider`.

### Services

**`ImageUploadService`** orchestrates an upload. Injected with `ImageProcessor` and `ImageStorageInterface`. `upload(UploadedFile $file): Image`:

1. Process the file with `ImageProcessingOptions(maxWidth: 1024, maxHeight: 1024)`.
2. Build a storage key `images/{uuid}.{jpg|png}`.
3. Store bytes through `ImageStorageInterface`.
4. Insert an `images` row (key, original filename, driver, content type, size, width, height) and return the model.

**`ImageProcessor`** validates and optionally resizes. `process(UploadedFile $file, ImageProcessingOptions $options): ProcessedImage`:

- Reads the file and requires a valid JPEG or PNG (`InvalidImageException` otherwise).
- If both dimensions already fit the max size, returns the original bytes (no re-encode).
- Otherwise scales down, keeping aspect ratio, preserving PNG transparency, and returns new bytes plus the new width/height.

**`LocalImageStorage`** writes to Laravel's `public` disk (`storage/app/public`). `put()` failure throws `RuntimeException`. Returns `StoredImage` with `StorageDriver::Local`.

**`AzureImageStorage`** is a stub. It returns `StoredImage` with `StorageDriver::Azure` but does not upload to Azure yet.
