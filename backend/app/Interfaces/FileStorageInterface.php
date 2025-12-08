<?php

namespace App\Interfaces;

use Illuminate\Http\UploadedFile;

interface FileStorageInterface
{
    /**
     * Store an uploaded file.
     *
     * @param UploadedFile $file
     * @param string $path
     * @param string $disk
     * @return string The path to the stored file.
     */
    public function store(UploadedFile $file, string $path, string $disk = null): string;

    /**
     * Delete a file.
     *
     * @param string $path
     * @param string $disk
     * @return bool
     */
    public function delete(string $path, string $disk = null): bool;

    /**
     * Get the URL of a file.
     *
     * @param string $path
     * @param string $disk
     * @return string
     */
    public function getUrl(string $path, string $disk = null): string;

    /**
     * Get a temporary URL for the file (for private files).
     * 
     * @param string $path
     * @param \DateTimeInterface $expiration
     * @param array $options
     * @param string $disk
     * @return string
     */
    public function getTemporaryUrl(string $path, $expiration, array $options = [], string $disk = null): string;
}
