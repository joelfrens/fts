<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import { onUnmounted, ref } from 'vue'

const file = ref<File | null>(null)
const previewUrl = ref<string | null>(null)
const uploading = ref(false)
const success = ref(false)
const error = ref<string | null>(null)

function handleFileChange(event: Event): void {
    clearPreview()

    success.value = false
    error.value = null

    const input = event.target as HTMLInputElement
    const selectedFile = input.files?.[0]

    if (!selectedFile) {
        file.value = null
        return
    }

    file.value = selectedFile
    previewUrl.value = URL.createObjectURL(selectedFile)
}

async function upload(): Promise<void> {
    if (!file.value || uploading.value) {
        return
    }

    uploading.value = true
    success.value = false
    error.value = null

    const formData = new FormData()
    formData.append('image', file.value)

    try {
        const response = await fetch('/api/images', {
            method: 'POST',
            headers: {
                Accept: 'application/json',
            },
            body: formData,
        })

        const data = await response.json()

        if (!response.ok) {
            throw new Error(
                data.errors?.image?.[0] ??
                data.message ??
                'Unable to upload image.'
            )
        }

        success.value = true
    } catch (exception) {
        error.value =
            exception instanceof Error
                ? exception.message
                : 'Unable to upload image.'
    } finally {
        uploading.value = false
    }
}

function clearPreview(): void {
    if (previewUrl.value) {
        URL.revokeObjectURL(previewUrl.value)
        previewUrl.value = null
    }
}

onUnmounted(clearPreview)
</script>

<template>
    <Head title="Image Upload" />

    <main class="page">
        <section class="card">
            <header>
                <h1>Upload an image</h1>
                <p>Choose a JPG or PNG image to upload.</p>
            </header>

            <label class="file-picker">
                Choose image

                <input
                    type="file"
                    accept="image/jpeg,image/png"
                    @change="handleFileChange"
                >
            </label>

            <div
                v-if="file && previewUrl"
                class="preview-container"
            >
                <img
                    :src="previewUrl"
                    alt="Selected image preview"
                    class="preview"
                >

                <div class="file-details">
                    <span>{{ file.name }}</span>

                    <span>
                        {{ (file.size / 1024 / 1024).toFixed(2) }} MB
                    </span>
                </div>
            </div>

            <button
                type="button"
                class="upload-button"
                :disabled="!file || uploading"
                @click="upload"
            >
                {{ uploading ? 'Uploading...' : 'Upload image' }}
            </button>

            <p
                v-if="success"
                class="message success"
            >
                Image uploaded successfully.
            </p>

            <p
                v-if="error"
                class="message error"
            >
                {{ error }}
            </p>
        </section>
    </main>
</template>

<style scoped>
.page {
    display: flex;
    min-height: 100vh;
    align-items: center;
    justify-content: center;
    padding: 24px;
    background: #f5f6f8;
    font-family: system-ui, sans-serif;
}

.card {
    width: 100%;
    max-width: 560px;
    padding: 32px;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    background: white;
    box-shadow: 0 8px 30px rgb(0 0 0 / 6%);
}

header {
    margin-bottom: 24px;
}

h1 {
    margin: 0 0 8px;
    color: #111827;
    font-size: 28px;
}

header p {
    margin: 0;
    color: #6b7280;
}

.file-picker {
    display: inline-block;
    padding: 10px 16px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    cursor: pointer;
}

.file-picker:hover {
    background: #f9fafb;
}

.file-picker input {
    display: none;
}

.preview-container {
    margin-top: 24px;
    overflow: hidden;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
}

.preview {
    display: block;
    width: 100%;
    max-height: 360px;
    background: #f9fafb;
    object-fit: contain;
}

.file-details {
    display: flex;
    justify-content: space-between;
    gap: 16px;
    padding: 12px 16px;
    color: #6b7280;
    font-size: 14px;
}

.file-details span:first-child {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.upload-button {
    width: 100%;
    margin-top: 24px;
    padding: 12px 18px;
    border: 0;
    border-radius: 8px;
    background: #111827;
    color: white;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
}

.upload-button:hover:not(:disabled) {
    background: #1f2937;
}

.upload-button:disabled {
    cursor: not-allowed;
    opacity: 0.5;
}

.message {
    margin: 16px 0 0;
    padding: 12px 16px;
    border-radius: 8px;
    font-size: 14px;
}

.success {
    background: #ecfdf5;
    color: #047857;
}

.error {
    background: #fef2f2;
    color: #b91c1c;
}
</style>