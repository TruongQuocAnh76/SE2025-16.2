<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\Storage\MinioStorageService;
use App\Contracts\StorageServiceInterface;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Mockery;

class MinioStorageServiceTest extends TestCase
{
    protected MinioStorageService $storageService;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Set up test environment variables
        config([
            'filesystems.disks.s3.key' => 'minioadmin',
            'filesystems.disks.s3.secret' => 'minioadmin',
            'filesystems.disks.s3.region' => 'us-east-1',
            'filesystems.disks.s3.bucket' => 'certchain-test',
            'filesystems.disks.s3.endpoint' => 'http://nginx:9002',
            'filesystems.disks.s3.use_path_style_endpoint' => true,
        ]);
        
        $this->storageService = new MinioStorageService('s3');
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function it_implements_storage_service_interface()
    {
        $this->assertInstanceOf(StorageServiceInterface::class, $this->storageService);
    }

    #[Test]
    public function it_generates_temporary_upload_url_with_correct_format()
    {
        // Set frontend endpoint for presigned URLs
        putenv('FRONTEND_STORAGE_ENDPOINT=http://localhost:9002');
        
        $path = 'courses/thumbnails/test-uuid.jpg';
        $expiry = now()->addMinutes(30);
        $options = ['ContentType' => 'image/jpeg'];

        $url = $this->storageService->temporaryUploadUrl($path, $expiry, $options);

        // Verify URL structure
        $this->assertIsString($url);
        $this->assertStringContainsString('http://localhost:9002', $url);
        $this->assertStringContainsString('certchain-test', $url);
        $this->assertStringContainsString($path, $url);
        
        // Verify presigned URL parameters
        $this->assertStringContainsString('X-Amz-Algorithm=AWS4-HMAC-SHA256', $url);
        $this->assertStringContainsString('X-Amz-Credential=', $url);
        $this->assertStringContainsString('X-Amz-Date=', $url);
        $this->assertStringContainsString('X-Amz-Expires=', $url);
        $this->assertStringContainsString('X-Amz-SignedHeaders=host', $url);
        $this->assertStringContainsString('X-Amz-Signature=', $url);
    }

    #[Test]
    public function it_generates_upload_url_for_video_files()
    {
        putenv('FRONTEND_STORAGE_ENDPOINT=http://localhost:9002');
        
        $path = 'courses/original-videos/uuid1/modules/uuid2/lessons/uuid3.mp4';
        $expiry = now()->addMinutes(120);
        $options = ['ContentType' => 'video/mp4'];

        $url = $this->storageService->temporaryUploadUrl($path, $expiry, $options);

        $this->assertIsString($url);
        $this->assertStringContainsString($path, $url);
        $this->assertStringContainsString('X-Amz-Signature=', $url);
    }

    #[Test]
    public function it_uses_frontend_endpoint_for_upload_urls()
    {
        // Set different endpoints for internal and frontend
        config(['filesystems.disks.s3.endpoint' => 'http://nginx:9002']);
        putenv('FRONTEND_STORAGE_ENDPOINT=http://localhost:9002');
        
        $storageService = new MinioStorageService('s3');
        
        $url = $storageService->temporaryUploadUrl(
            'test/file.jpg',
            now()->addMinutes(5),
            ['ContentType' => 'image/jpeg']
        );

        // Should use frontend endpoint, not internal
        $this->assertStringContainsString('http://localhost:9002', $url);
        $this->assertStringNotContainsString('nginx', $url);
    }

    #[Test]
    public function it_generates_different_signatures_for_different_paths()
    {
        putenv('FRONTEND_STORAGE_ENDPOINT=http://localhost:9002');
        
        $url1 = $this->storageService->temporaryUploadUrl(
            'path/file1.jpg',
            now()->addMinutes(5),
            ['ContentType' => 'image/jpeg']
        );
        
        $url2 = $this->storageService->temporaryUploadUrl(
            'path/file2.jpg',
            now()->addMinutes(5),
            ['ContentType' => 'image/jpeg']
        );

        // Extract signatures
        preg_match('/X-Amz-Signature=([a-f0-9]+)/', $url1, $matches1);
        preg_match('/X-Amz-Signature=([a-f0-9]+)/', $url2, $matches2);
        
        $this->assertNotEquals($matches1[1], $matches2[1]);
    }

    #[Test]
    public function it_returns_bucket_name()
    {
        $bucket = $this->storageService->getBucket();
        
        $this->assertEquals('certchain-test', $bucket);
    }

    #[Test]
    public function it_returns_disk_name()
    {
        $disk = $this->storageService->getDisk();
        
        $this->assertEquals('s3', $disk);
    }

    #[Test]
    public function upload_url_is_for_put_operation()
    {
        putenv('FRONTEND_STORAGE_ENDPOINT=http://localhost:9002');
        
        $path = 'test/file.jpg';
        $expiry = now()->addMinutes(5);
        
        // Generate upload URL (PUT operation)
        $putUrl = $this->storageService->temporaryUploadUrl($path, $expiry, ['ContentType' => 'image/jpeg']);
        
        // The URL should contain the path and bucket
        $this->assertStringContainsString('certchain-test', $putUrl);
        $this->assertStringContainsString($path, $putUrl);
        
        // Should have a valid signature
        preg_match('/X-Amz-Signature=([a-f0-9]+)/', $putUrl, $putMatches);
        $this->assertNotEmpty($putMatches);
        $this->assertEquals(64, strlen($putMatches[1]), 'Signature should be 64 characters (SHA256)');
    }
}
