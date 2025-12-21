<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    /**
     * @OA\Post(
     *     path="/media/upload",
     *     summary="Upload a file",
     *     tags={"Media"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="file",
     *                     type="string",
     *                     format="binary",
     *                     description="File to upload (max 2MB, images only)"
     *                 ),
     *                 @OA\Property(property="folder", type="string", description="Optional folder name", example="avatars")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="File uploaded successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="url", type="string", example="http://localhost:9000/bucket/avatars/file.jpg"),
     *             @OA\Property(property="path", type="string", example="avatars/file.jpg")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|image|max:2048', // Max 2MB, image types
            'folder' => 'nullable|string|max:50'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $file = $request->file('file');
        $folder = $request->input('folder', 'uploads');
        
        // Generate unique filename
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = "$folder/$filename";

        // Store file (using default disk, usually 'public' or 's3')
        // We use 'public' disk for local dev simplicity if S3 not configured, 
        // but 's3' is better for this project structure. 
        // Let's fallback to 'public' if 's3' isn't standard, but this project seems to use MinIO.
        // Checking .env logic, usually FILESYSTEM_DISK is set.
        
        $disk = config('filesystems.default', 'public');
        
        $path = $file->storeAs($folder, $filename, $disk);
        
        $url = Storage::disk($disk)->url($path);

        return response()->json([
            'url' => $url,
            'path' => $path,
            'disk' => $disk
        ]);
    }
}
