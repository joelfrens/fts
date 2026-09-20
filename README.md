<p align="center">
    <img src="public/favicon.svg" width="120" alt="FTS">
</p>

# FTS

Laravel image storage app with pluggable drivers (local and Azure).

## Architecture

![Upload flow from the Frontend UI through the Laravel Images API, then Image Upload Service, Image Processor, and ImageUpload Interface to Azure or local storage, with a database audit record](docs/architecture.png)
