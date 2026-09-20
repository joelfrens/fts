<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreImageRequest;
use App\Services\ImageUploadService;
use Illuminate\Http\JsonResponse;

final class ImageController extends Controller
{
    public function store(
        StoreImageRequest $request,
        ImageUploadService $imageUploadService,
    ): JsonResponse {
        $image = $imageUploadService->upload(
            $request->file('image')
        );

        return response()->json([
            'data' => [
                'id' => $image->id,
                'storage_key' => $image->storage_key,
                'content_type' => $image->content_type,
                'width' => $image->width,
                'height' => $image->height,
            ],
        ], 201);
    }
}