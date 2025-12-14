<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\Storage\MinioStorageService;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;

/**
 * Integration tests that require a running MinIO instance.
 * These tests verify the actual upload flow works correctly.
 * 
 * Run these tests with: php artisan test --filter StorageIntegrationTest
 * Requires: MinIO running on the configured endpoint
 */
class StorageIntegrationTest extends TestCase
{
    protected MinioStorageService $storage;
    protected bool $minioAvailable = false;
    protected string $internalEndpoint;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Use internal endpoint for actual HTTP calls from within container
        $this->internalEndpoint = env('STORAGE_ENDPOINT', 'http://nginx:9002');
        
        // Configure storage
        config([
            'filesystems.disks.s3.key' => env('STORAGE_ACCESS_KEY', 'minioadmin'),
            'filesystems.disks.s3.secret' => env('STORAGE_SECRET_KEY', 'minioadmin'),
            'filesystems.disks.s3.region' => env('STORAGE_REGION', 'us-east-1'),
            'filesystems.disks.s3.bucket' => env('STORAGE_BUCKET', 'certchain-dev'),
            'filesystems.disks.s3.endpoint' => $this->internalEndpoint,
            'filesystems.disks.s3.use_path_style_endpoint' => true,
        ]);
        
        // For URL generation tests, use frontend endpoint
        putenv('FRONTEND_STORAGE_ENDPOINT=' . env('FRONTEND_STORAGE_ENDPOINT', 'http://localhost:9002'));
        
        $this->storage = new MinioStorageService('s3');
        
        // Check if MinIO is available
        try {
            $this->storage->files('test-connection-check');
            $this->minioAvailable = true;
        } catch (\Exception $e) {
            $this->minioAvailable = false;
        }
    }

    /**
     * Generate upload URL using internal endpoint (for testing from within container)
     * Creates a fresh S3Client with the internal endpoint for HTTP calls from container
     */
    protected function generateInternalUploadUrl(string $path, \DateTimeInterface $expiry, array $options = []): string
    {
        // Create S3 client directly with internal endpoint for testing
        $client = new \Aws\S3\S3Client([
            'version' => 'latest',
            'region' => config('filesystems.disks.s3.region', 'us-east-1'),
            'endpoint' => $this->internalEndpoint,
            'use_path_style_endpoint' => true,
            'credentials' => [
                'key' => config('filesystems.disks.s3.key'),
                'secret' => config('filesystems.disks.s3.secret'),
            ],
        ]);

        $commandOptions = [
            'Bucket' => config('filesystems.disks.s3.bucket'),
            'Key' => $path,
        ];

        if (isset($options['ContentType'])) {
            $commandOptions['ContentType'] = $options['ContentType'];
        }

        $cmd = $client->getCommand('PutObject', $commandOptions);
        
        $now = new \DateTime();
        $diffSeconds = $expiry->getTimestamp() - $now->getTimestamp();
        $duration = '+' . max(1, $diffSeconds) . ' seconds';

        $request = $client->createPresignedRequest($cmd, $duration);
        
        return (string) $request->getUri();
    }

    #[Test]
    public function generated_upload_url_allows_put_request()
    {
        if (!$this->minioAvailable) {
            $this->markTestSkipped('MinIO is not available');
        }

        $testPath = 'tests/upload-test-' . uniqid() . '.txt';
        $testContent = 'Test content for upload: ' . date('Y-m-d H:i:s');
        
        // Generate upload URL using internal endpoint
        $uploadUrl = $this->generateInternalUploadUrl(
            $testPath,
            now()->addMinutes(5),
            ['ContentType' => 'text/plain']
        );

        // Perform actual upload using the presigned URL
        $response = Http::withBody($testContent, 'text/plain')
            ->put($uploadUrl);

        $this->assertTrue($response->successful(), 'Upload should succeed. Response: ' . $response->body());
        
        // Verify file exists
        $this->assertTrue($this->storage->exists($testPath), 'File should exist after upload');
        
        // Verify content
        $downloadedContent = $this->storage->get($testPath);
        $this->assertEquals($testContent, $downloadedContent);
        
        // Cleanup
        $this->storage->delete($testPath);
    }

    #[Test]
    public function generated_upload_url_works_for_image_files()
    {
        if (!$this->minioAvailable) {
            $this->markTestSkipped('MinIO is not available');
        }

        $testPath = 'tests/thumbnails/test-image-' . uniqid() . '.jpg';
        
        // Create a simple fake JPEG (minimal valid JPEG header)
        $fakeJpeg = "\xFF\xD8\xFF\xE0\x00\x10JFIF\x00\x01\x01\x00\x00\x01\x00\x01\x00\x00" . 
                    str_repeat("\x00", 100) . "\xFF\xD9";
        
        // Generate upload URL for image using internal endpoint
        $uploadUrl = $this->generateInternalUploadUrl(
            $testPath,
            now()->addMinutes(5),
            ['ContentType' => 'image/jpeg']
        );

        // Perform upload
        $response = Http::withBody($fakeJpeg, 'image/jpeg')
            ->put($uploadUrl);

        $this->assertTrue($response->successful(), 'Image upload should succeed');
        $this->assertTrue($this->storage->exists($testPath), 'Image should exist after upload');
        
        // Cleanup
        $this->storage->delete($testPath);
    }

    #[Test]
    public function generated_upload_url_works_for_video_files()
    {
        if (!$this->minioAvailable) {
            $this->markTestSkipped('MinIO is not available');
        }

        $testPath = 'tests/videos/test-video-' . uniqid() . '.mp4';
        
        // Create fake MP4 content (just some bytes, not a real video)
        $fakeVideo = str_repeat("\x00", 1024);
        
        // Generate upload URL for video using internal endpoint
        $uploadUrl = $this->generateInternalUploadUrl(
            $testPath,
            now()->addMinutes(5),
            ['ContentType' => 'video/mp4']
        );

        // Perform upload
        $response = Http::withBody($fakeVideo, 'video/mp4')
            ->put($uploadUrl);

        $this->assertTrue($response->successful(), 'Video upload should succeed');
        $this->assertTrue($this->storage->exists($testPath), 'Video should exist after upload');
        
        // Verify file size
        $size = $this->storage->size($testPath);
        $this->assertEquals(1024, $size);
        
        // Cleanup
        $this->storage->delete($testPath);
    }

    #[Test]
    public function upload_url_expires_after_timeout()
    {
        if (!$this->minioAvailable) {
            $this->markTestSkipped('MinIO is not available');
        }

        $testPath = 'tests/expiry-test-' . uniqid() . '.txt';
        
        // Generate URL that expires in 1 second using internal endpoint
        $uploadUrl = $this->generateInternalUploadUrl(
            $testPath,
            now()->addSeconds(1),
            ['ContentType' => 'text/plain']
        );

        // Wait for expiry
        sleep(2);

        // Try to upload - should fail
        $response = Http::withBody('test', 'text/plain')
            ->put($uploadUrl);

        $this->assertFalse($response->successful(), 'Upload should fail after URL expiry');
        $this->assertFalse($this->storage->exists($testPath), 'File should not exist');
    }

    #[Test]
    public function upload_fails_with_tampered_signature()
    {
        if (!$this->minioAvailable) {
            $this->markTestSkipped('MinIO is not available');
        }

        $testPath = 'tests/tampered-test-' . uniqid() . '.txt';
        
        $uploadUrl = $this->generateInternalUploadUrl(
            $testPath,
            now()->addMinutes(5),
            ['ContentType' => 'text/plain']
        );

        // Tamper with the signature
        $tamperedUrl = preg_replace('/X-Amz-Signature=[a-f0-9]+/', 'X-Amz-Signature=invalid', $uploadUrl);

        $response = Http::withBody('test', 'text/plain')
            ->put($tamperedUrl);

        $this->assertFalse($response->successful(), 'Upload should fail with tampered signature');
        $this->assertEquals(403, $response->status());
    }

    #[Test]
    public function file_existence_check_works()
    {
        if (!$this->minioAvailable) {
            $this->markTestSkipped('MinIO is not available');
        }

        $existingPath = 'tests/existence-test-' . uniqid() . '.txt';
        $nonExistingPath = 'tests/non-existing-' . uniqid() . '.txt';
        
        // Upload a file first
        $this->storage->put($existingPath, 'test content');
        
        // Check existence
        $this->assertTrue($this->storage->exists($existingPath));
        $this->assertFalse($this->storage->exists($nonExistingPath));
        
        // Cleanup
        $this->storage->delete($existingPath);
    }

    #[Test]
    public function storage_helper_builds_correct_frontend_url()
    {
        putenv('FRONTEND_STORAGE_ENDPOINT=http://localhost:9002');
        putenv('STORAGE_BUCKET=certchain-dev');
        
        $url = \App\Helpers\StorageUrlHelper::buildFullUrl('courses/thumbnails/test.jpg');
        
        $this->assertEquals('http://localhost:9002/certchain-dev/courses/thumbnails/test.jpg', $url);
    }

    #[Test]
    public function frontend_upload_url_uses_frontend_endpoint()
    {
        putenv('FRONTEND_STORAGE_ENDPOINT=http://localhost:9002');
        
        $url = $this->storage->temporaryUploadUrl(
            'test/file.jpg',
            now()->addMinutes(5),
            ['ContentType' => 'image/jpeg']
        );
        
        // URL should use frontend endpoint (for browser access)
        $this->assertStringContainsString('http://localhost:9002', $url);
        $this->assertStringNotContainsString('nginx', $url);
    }

    protected function tearDown(): void
    {
        // Clean up any test files that might have been left behind
        if ($this->minioAvailable) {
            try {
                $testFiles = $this->storage->files('tests', true);
                foreach ($testFiles as $file) {
                    $this->storage->delete($file);
                }
            } catch (\Exception $e) {
                // Ignore cleanup errors
            }
        }
        
        parent::tearDown();
    }
}
