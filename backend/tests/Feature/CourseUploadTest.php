<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Course;
use App\Models\Module;
use App\Models\Lesson;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\Attributes\Test;

class CourseUploadTest extends TestCase
{
    use RefreshDatabase;

    protected User $instructor;
    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create an instructor user with correct schema (first_name, last_name instead of name)
        // Valid roles are: STUDENT, TEACHER, ADMIN (not INSTRUCTOR)
        $this->instructor = User::create([
            'first_name' => 'Test',
            'last_name' => 'Instructor',
            'username' => 'testinstructor',
            'role' => 'TEACHER',
            'email' => 'instructor@test.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);
        
        // Get auth token
        $this->token = $this->instructor->createToken('test-token')->plainTextToken;

        // Configure storage for testing
        config([
            'filesystems.disks.s3.key' => env('STORAGE_ACCESS_KEY', 'minioadmin'),
            'filesystems.disks.s3.secret' => env('STORAGE_SECRET_KEY', 'minioadmin'),
            'filesystems.disks.s3.region' => env('STORAGE_REGION', 'us-east-1'),
            'filesystems.disks.s3.bucket' => env('STORAGE_BUCKET', 'certchain-dev'),
            'filesystems.disks.s3.endpoint' => env('STORAGE_ENDPOINT', 'http://nginx:9002'),
            'filesystems.disks.s3.use_path_style_endpoint' => true,
        ]);
        
        putenv('FRONTEND_STORAGE_ENDPOINT=' . env('FRONTEND_STORAGE_ENDPOINT', 'http://localhost:9002'));
    }

    #[Test]
    public function course_creation_returns_thumbnail_upload_url_when_requested()
    {
        $courseData = [
            'title' => 'Test Course',
            'description' => 'Test description',
            'price' => 99.99,
            'thumbnail' => 'UPLOAD_REQUESTED',
            'modules' => []
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->postJson('/api/courses', $courseData);

        $response->assertStatus(201);
        
        // Check that thumbnail_upload_url is returned
        $response->assertJsonStructure([
            'course',
            'thumbnail_upload_url'
        ]);
        
        $thumbnailUrl = $response->json('thumbnail_upload_url');
        
        // Verify it's a valid presigned URL
        $this->assertNotNull($thumbnailUrl);
        $this->assertStringContainsString('X-Amz-Signature=', $thumbnailUrl);
        $this->assertStringContainsString('X-Amz-Expires=', $thumbnailUrl);
    }

    #[Test]
    public function course_creation_returns_video_upload_urls_for_lessons()
    {
        $courseData = [
            'title' => 'Test Course with Videos',
            'description' => 'Test description',
            'price' => 99.99,
            'thumbnail' => 'UPLOAD_REQUESTED',
            'modules' => [
                [
                    'title' => 'Module 1',
                    'description' => 'Module description',
                    'order_index' => 0,
                    'lessons' => [
                        [
                            'title' => 'Lesson 1',
                            'content_type' => 'VIDEO',
                            'content_url' => '', // Empty to trigger upload URL generation
                            'order_index' => 0,
                            'is_free' => false,
                        ]
                    ]
                ]
            ]
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->postJson('/api/courses', $courseData);

        $response->assertStatus(201);
        
        // Check that video_upload_urls is returned
        $response->assertJsonStructure([
            'course',
            'video_upload_urls'
        ]);
        
        $videoUrls = $response->json('video_upload_urls');
        
        // Should have at least one video upload URL
        $this->assertNotEmpty($videoUrls);
        
        // Check first lesson's upload URL
        $firstKey = array_key_first($videoUrls);
        $this->assertArrayHasKey('upload_url', $videoUrls[$firstKey]);
        $this->assertArrayHasKey('lesson_id', $videoUrls[$firstKey]);
        $this->assertArrayHasKey('original_video_path', $videoUrls[$firstKey]);
        
        // Verify it's a valid presigned URL
        $uploadUrl = $videoUrls[$firstKey]['upload_url'];
        $this->assertStringContainsString('X-Amz-Signature=', $uploadUrl);
    }

    #[Test]
    public function presigned_upload_url_contains_correct_bucket_and_path()
    {
        $courseData = [
            'title' => 'Test Course',
            'description' => 'Test description',
            'price' => 99.99,
            'thumbnail' => 'UPLOAD_REQUESTED',
            'modules' => []
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->postJson('/api/courses', $courseData);

        $response->assertStatus(201);
        
        $thumbnailUrl = $response->json('thumbnail_upload_url');
        $courseId = $response->json('course.id');
        
        // Verify URL contains expected path structure
        $expectedBucket = config('filesystems.disks.s3.bucket');
        $this->assertStringContainsString($expectedBucket, $thumbnailUrl);
        $this->assertStringContainsString('courses/thumbnails/', $thumbnailUrl);
        $this->assertStringContainsString($courseId, $thumbnailUrl);
    }

    #[Test]
    public function course_without_upload_request_does_not_return_upload_url()
    {
        $courseData = [
            'title' => 'Test Course',
            'description' => 'Test description',
            'price' => 99.99,
            'thumbnail' => '', // Empty, not UPLOAD_REQUESTED
            'modules' => []
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->postJson('/api/courses', $courseData);

        $response->assertStatus(201);
        
        // thumbnail_upload_url should be null
        $this->assertNull($response->json('thumbnail_upload_url'));
    }

    #[Test]
    public function presigned_urls_expire_parameter_is_set()
    {
        $courseData = [
            'title' => 'Test Course',
            'description' => 'Test description',
            'price' => 99.99,
            'thumbnail' => 'UPLOAD_REQUESTED',
            'modules' => [
                [
                    'title' => 'Module 1',
                    'description' => 'Module description',
                    'order_index' => 0,
                    'lessons' => [
                        [
                            'title' => 'Lesson 1',
                            'content_type' => 'VIDEO',
                            'content_url' => '',
                            'order_index' => 0,
                            'is_free' => false,
                        ]
                    ]
                ]
            ]
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->postJson('/api/courses', $courseData);

        $response->assertStatus(201);
        
        $thumbnailUrl = $response->json('thumbnail_upload_url');
        $videoUrls = $response->json('video_upload_urls');
        
        // Thumbnail URL should have ~30 minutes expiry (1800 seconds)
        preg_match('/X-Amz-Expires=(\d+)/', $thumbnailUrl, $thumbnailExpiry);
        $this->assertGreaterThanOrEqual(1700, (int)$thumbnailExpiry[1]); // ~30 min
        $this->assertLessThanOrEqual(1900, (int)$thumbnailExpiry[1]);
        
        // Video URL should have ~2 hours expiry (7200 seconds)
        $firstVideoUrl = $videoUrls[array_key_first($videoUrls)]['upload_url'];
        preg_match('/X-Amz-Expires=(\d+)/', $firstVideoUrl, $videoExpiry);
        $this->assertGreaterThanOrEqual(7000, (int)$videoExpiry[1]); // ~2 hours
        $this->assertLessThanOrEqual(7400, (int)$videoExpiry[1]);
    }

    #[Test]
    public function unauthenticated_user_cannot_create_course()
    {
        $courseData = [
            'title' => 'Test Course',
            'description' => 'Test description',
            'price' => 99.99,
        ];

        $response = $this->postJson('/api/courses', $courseData);

        $response->assertStatus(401);
    }
}
