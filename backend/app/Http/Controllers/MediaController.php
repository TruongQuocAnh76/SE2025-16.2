<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Contracts\StorageServiceInterface;
use App\Helpers\StorageUrlHelper;

class MediaController extends Controller
{
    protected StorageServiceInterface $storage;

    public function __construct(StorageServiceInterface $storage)
    {
        $this->storage = $storage;
    }

    /**
     * Generate a pre-signed URL for file upload.
     * 
     * @OA\Post(
     *     path="/media/presigned-url",
     *     summary="Get presigned upload URL",
     *     tags={"Media"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"extension","content_type"},
     *             @OA\Property(property="folder", type="string", enum={"avatars","courses","assignments"}, default="avatars"),
     *             @OA\Property(property="extension", type="string", example="jpg"),
     *             @OA\Property(property="content_type", type="string", example="image/jpeg")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Presigned URL generated",
     *         @OA\JsonContent(
     *             @OA\Property(property="upload_url", type="string"),
     *             @OA\Property(property="path", type="string"),
     *             @OA\Property(property="url", type="string")
     *         )
     *     )
     * )
     */
    public function getPresignedUrl(Request $request)
    {
        $request->validate([
            'folder' => 'required|string|in:avatars,courses,assignments',
            'extension' => 'required|string|max:10',
            'content_type' => 'required|string'
        ]);

        $folder = $request->input('folder');
        $extension = $request->input('extension');
        $contentType = $request->input('content_type');

        // Generate a random filename
        $filename = Str::random(40) . '.' . $extension;
        $path = $folder . '/' . $filename;

        // Generate pre-signed upload URL (valid for 30 minutes)
        $uploadUrl = $this->storage->temporaryUploadUrl(
            $path,
            now()->addMinutes(30),
            ['ContentType' => $contentType]
        );

        // Calculate the expected public URL
        $publicUrl = StorageUrlHelper::buildFullUrl($path);

        return response()->json([
            'upload_url' => $uploadUrl,
            'path' => $path,
            'url' => $publicUrl
        ]);
    }
}
