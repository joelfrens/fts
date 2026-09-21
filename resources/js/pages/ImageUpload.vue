<script setup lang="ts">
import { ref } from 'vue'

interface UploadedImage {
    id: number
    url: string
    content_type: string
    width: number
    height: number
}

interface UploadResponse {
    data: UploadedImage
}

const ALLOWED_IMAGE_TYPES = [
    'image/jpeg',
    'image/png',
]

const MAX_FILE_SIZE = 10 * 1024 * 1024

const file = ref<File | null>(null)
const fileInput = ref<HTMLInputElement | null>(null)
const uploadedImage = ref<UploadedImage | null>(null)

const uploading = ref(false)
const success = ref(false)
const error = ref<string | null>(null)

function handleFileChange(event: Event): void {
    success.value = false
    error.value = null
    uploadedImage.value = null

    const input = event.target as HTMLInputElement
    const selectedFile = input.files?.[0]

    if (!selectedFile) {
        file.value = null
        return
    }

    if (!ALLOWED_IMAGE_TYPES.includes(selectedFile.type)) {
        file.value = null
        input.value = ''

        error.value = 'Please select a JPG or PNG image.'

        return
    }

    if (selectedFile.size > MAX_FILE_SIZE) {
        file.value = null
        input.value = ''

        error.value =
            'The selected image is too large. Maximum upload size is 10 MB.'

        return
    }

    file.value = selectedFile
}

async function uploadImage(): Promise<void> {
    if (!file.value || uploading.value) {
        return
    }

    uploading.value = true
    success.value = false
    error.value = null
    uploadedImage.value = null

    const formData = new FormData()

    formData.append(
        'image',
        file.value,
    )

    try {
        const response = await fetch('/api/images', {
            method: 'POST',
            headers: {
                Accept: 'application/json',
            },
            body: formData,
        })

        if (response.status === 413) {
            throw new Error(
                'The selected image is too large. Maximum upload size is 1 MB.'
            )
        }

        const result = await response.json()

        if (!response.ok) {
            throw new Error(
                result.errors?.image?.[0] ??
                    result.message ??
                    'Unable to upload image.'
            )
        }

        const uploadResponse =
            result as UploadResponse

        uploadedImage.value =
            uploadResponse.data

        success.value = true

        // Clear the selected file after a successful upload.
        file.value = null

        if (fileInput.value) {
            fileInput.value.value = ''
        }
    } catch (exception) {
        error.value =
            exception instanceof Error
                ? exception.message
                : 'Unable to upload image.'
    } finally {
        uploading.value = false
    }
}

function formatFileSize(bytes: number): string {
    if (bytes < 1024) {
        return `${bytes} B`
    }

    if (bytes < 1024 * 1024) {
        return `${(bytes / 1024).toFixed(1)} KB`
    }

    return `${(
        bytes /
        (1024 * 1024)
    ).toFixed(1)} MB`
}
</script>

<template>
    <main class="page">
        <section class="upload-card">
            <header class="header">
                <h1>Upload image</h1>

                <p>
                    Upload a JPG or PNG image. Images larger
                    than 1024 × 1024 will be resized while
                    preserving their aspect ratio.
                </p>
            </header>

            <form
                class="upload-form"
                @submit.prevent="uploadImage"
            >
                <div class="file-section">
                    <input
                        id="image"
                        ref="fileInput"
                        type="file"
                        accept="image/jpeg,image/png"
                        class="file-input"
                        @change="handleFileChange"
                    >

                    <label
                        for="image"
                        class="choose-button"
                    >
                        Choose image
                    </label>

                    <div
                        v-if="file"
                        class="selected-file"
                    >
                        <p class="file-name">
                            {{ file.name }}
                        </p>

                        <p class="file-size">
                            {{ formatFileSize(file.size) }}
                        </p>
                    </div>
                </div>

                <div
                    v-if="error"
                    class="message error-message"
                    role="alert"
                >
                    {{ error }}
                </div>

                <div class="actions">
                    <button
                        type="submit"
                        class="upload-button"
                        :disabled="!file || uploading"
                    >
                        {{
                            uploading
                                ? 'Uploading...'
                                : 'Upload image'
                        }}
                    </button>
                </div>
            </form>

            <section
                v-if="success && uploadedImage"
                class="uploaded-section"
            >
                <div
                    class="message success-message"
                    role="status"
                >
                    Image uploaded successfully.
                </div>

                <div class="uploaded-content">
                    <h2>Uploaded image</h2>

                    <img
                        :src="uploadedImage.url"
                        alt="Uploaded image"
                        class="uploaded-image"
                    >

                    <div class="image-details">
                        <p>
                            {{ uploadedImage.width }}
                            ×
                            {{ uploadedImage.height }}
                        </p>

                        <p>
                            {{ uploadedImage.content_type }}
                        </p>
                    </div>

                    <a
                        :href="uploadedImage.url"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="image-link"
                    >
                        Open uploaded image
                    </a>
                </div>
            </section>
        </section>
    </main>
</template>

<style scoped>
.page {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px;
    background: #f9fafb;
    box-sizing: border-box;
}

.upload-card {
    width: 100%;
    max-width: 640px;
    padding: 32px;
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    box-shadow:
        0 1px 2px rgba(0, 0, 0, 0.04),
        0 4px 12px rgba(0, 0, 0, 0.04);
}

.header {
    text-align: center;
}

.header h1 {
    margin: 0;
    color: #111827;
    font-size: 24px;
    font-weight: 600;
    line-height: 1.3;
}

.header p {
    max-width: 500px;
    margin: 10px auto 0;
    color: #6b7280;
    font-size: 14px;
    line-height: 1.6;
}

.upload-form {
    margin-top: 32px;
}

.file-section {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.file-input {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
}

.choose-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 10px 20px;
    color: #374151;
    background: #ffffff;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition:
        background 0.15s ease,
        border-color 0.15s ease;
}

.choose-button:hover {
    background: #f9fafb;
    border-color: #9ca3af;
}

.choose-button:focus-within {
    outline: 2px solid #111827;
    outline-offset: 2px;
}

.selected-file {
    margin-top: 16px;
    text-align: center;
}

.selected-file p {
    margin: 0;
}

.file-name {
    color: #111827;
    font-size: 14px;
    font-weight: 500;
    overflow-wrap: anywhere;
}

.file-size {
    margin-top: 4px !important;
    color: #6b7280;
    font-size: 12px;
}

.actions {
    display: flex;
    justify-content: center;
    margin-top: 24px;
}

.upload-button {
    padding: 10px 24px;
    color: #ffffff;
    background: #111827;
    border: 0;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition:
        background 0.15s ease,
        opacity 0.15s ease;
}

.upload-button:hover:not(:disabled) {
    background: #374151;
}

.upload-button:focus {
    outline: 2px solid #111827;
    outline-offset: 2px;
}

.upload-button:disabled {
    cursor: not-allowed;
    opacity: 0.5;
}

.message {
    padding: 12px 16px;
    border-radius: 6px;
    font-size: 14px;
    line-height: 1.5;
    text-align: center;
}

.error-message {
    margin-top: 24px;
    color: #991b1b;
    background: #fef2f2;
    border: 1px solid #fecaca;
}

.success-message {
    color: #166534;
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
}

.uploaded-section {
    margin-top: 32px;
    padding-top: 32px;
    border-top: 1px solid #e5e7eb;
}

.uploaded-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-top: 24px;
}

.uploaded-content h2 {
    margin: 0;
    color: #111827;
    font-size: 18px;
    font-weight: 600;
}

.uploaded-image {
    display: block;
    width: auto;
    height: auto;
    max-width: 100%;
    max-height: 500px;
    margin-top: 16px;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    object-fit: contain;
}

.image-details {
    margin-top: 16px;
    color: #6b7280;
    font-size: 13px;
    text-align: center;
}

.image-details p {
    margin: 3px 0;
}

.image-link {
    margin-top: 16px;
    color: #111827;
    font-size: 14px;
    font-weight: 500;
    text-decoration: underline;
    text-underline-offset: 3px;
}

.image-link:hover {
    color: #4b5563;
}

@media (max-width: 640px) {
    .page {
        padding: 16px;
        align-items: flex-start;
    }

    .upload-card {
        margin-top: 24px;
        padding: 24px 20px;
    }

    .header h1 {
        font-size: 22px;
    }

    .uploaded-image {
        max-height: 400px;
    }
}
</style>