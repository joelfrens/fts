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
