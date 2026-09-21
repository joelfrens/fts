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
        $uploadedImage = $imageUploadService->upload(
            $request->file('image')
        );

        return response()->json([
            'data' => [
                'id' => $uploadedImage->image->id,
                'url' => $uploadedImage->url,
                'content_type' => $uploadedImage->image->content_type,
                'width' => $uploadedImage->image->width,
                'height' => $uploadedImage->image->height,
            ],
        ], 201);
    }
}