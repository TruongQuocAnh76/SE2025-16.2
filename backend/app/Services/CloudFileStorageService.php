<?php

namespace App\Services;

use App\Interfaces\FileStorageInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class CloudFileStorageService implements FileStorageInterface
{
    protected function getDisk(?string $disk = null)
    {
        return Storage::disk($disk ?? config('filesystems.cloud'));
    }

    public function store(UploadedFile $file, string $path, string $disk = null): string
    {
        return $this->getDisk($disk)->put($path, $file);
    }

    public function delete(string $path, string $disk = null): bool
    {
        return $this->getDisk($disk)->delete($path);
    }

    public function getUrl(string $path, string $disk = null): string
    {
        return $this->getDisk($disk)->url($path);
    }

    public function getTemporaryUrl(string $path, $expiration, array $options = [], string $disk = null): string
    {
        return $this->getDisk($disk)->temporaryUrl($path, $expiration, $options);
    }
}
