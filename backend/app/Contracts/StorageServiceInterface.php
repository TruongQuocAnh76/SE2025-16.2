<?php

namespace App\Contracts;

interface StorageServiceInterface
{
    /**
     * Store a file in the storage system
     *
     * @param string $path The path where the file should be stored
     * @param string|resource $contents The file contents
     * @param array $options Additional options (ContentType, CacheControl, visibility, etc.)
     * @return bool True if the file was stored successfully
     */
    public function put(string $path, $contents, array $options = []): bool;

    /**
     * Get the contents of a file
     *
     * @param string $path The path to the file
     * @return string The file contents
     * @throws \Exception If the file does not exist
     */
    public function get(string $path): string;

    /**
     * Check if a file exists
     *
     * @param string $path The path to check
     * @return bool True if the file exists
     */
    public function exists(string $path): bool;

    /**
     * Delete a file from storage
     *
     * @param string $path The path to the file to delete
     * @return bool True if the file was deleted successfully
     */
    public function delete(string $path): bool;

    /**
     * Delete multiple files from storage
     *
     * @param array $paths Array of paths to delete
     * @return bool True if all files were deleted successfully
     */
    public function deleteMultiple(array $paths): bool;

    /**
     * Generate a temporary/pre-signed URL for a file (GET request)
     *
     * @param string $path The path to the file
     * @param \DateTimeInterface $expiry When the URL should expire
     * @param array $options Additional options
     * @return string The temporary URL
     */
    public function temporaryUrl(string $path, \DateTimeInterface $expiry, array $options = []): string;

    /**
     * Generate a temporary/pre-signed URL for uploading a file (PUT request)
     *
     * @param string $path The path where the file will be uploaded
     * @param \DateTimeInterface $expiry When the URL should expire
     * @param array $options Additional options (ContentType, etc.)
     * @return string The pre-signed upload URL
     */
    public function temporaryUploadUrl(string $path, \DateTimeInterface $expiry, array $options = []): string;

    /**
     * Get the public URL for a file
     *
     * @param string $path The path to the file
     * @return string The public URL
     */
    public function url(string $path): string;

    /**
     * Get the size of a file in bytes
     *
     * @param string $path The path to the file
     * @return int The file size in bytes
     */
    public function size(string $path): int;

    /**
     * Get the MIME type of a file
     *
     * @param string $path The path to the file
     * @return string The MIME type
     */
    public function mimeType(string $path): string;

    /**
     * Get the last modified time of a file
     *
     * @param string $path The path to the file
     * @return int Unix timestamp of last modification
     */
    public function lastModified(string $path): int;

    /**
     * Copy a file to a new location
     *
     * @param string $from Source path
     * @param string $to Destination path
     * @return bool True if the file was copied successfully
     */
    public function copy(string $from, string $to): bool;

    /**
     * Move a file to a new location
     *
     * @param string $from Source path
     * @param string $to Destination path
     * @return bool True if the file was moved successfully
     */
    public function move(string $from, string $to): bool;

    /**
     * Get all files in a directory
     *
     * @param string $directory The directory path
     * @param bool $recursive Whether to list files recursively
     * @return array Array of file paths
     */
    public function files(string $directory = '', bool $recursive = false): array;

    /**
     * Get all directories within a directory
     *
     * @param string $directory The directory path
     * @param bool $recursive Whether to list directories recursively
     * @return array Array of directory paths
     */
    public function directories(string $directory = '', bool $recursive = false): array;

    /**
     * Create a directory
     *
     * @param string $path The directory path to create
     * @return bool True if the directory was created successfully
     */
    public function makeDirectory(string $path): bool;

    /**
     * Delete a directory
     *
     * @param string $path The directory path to delete
     * @return bool True if the directory was deleted successfully
     */
    public function deleteDirectory(string $path): bool;
}
