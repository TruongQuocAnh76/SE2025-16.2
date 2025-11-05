<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

use App\Models\Course;
use App\Models\Module;
use App\Models\Lesson;
use App\Models\Enrollment;
use App\Models\Review;
use App\Models\User;
use App\Models\Quiz;
use App\Models\Question;
use App\Services\CourseService;

/**
 * @OA\Server(
 *     url="http://localhost:8000/api",
 *     description="Local development server"
 * )
 */
class CourseController extends Controller
{
    protected $courseService;
    
    public function __construct(CourseService $courseService)
    {
        $this->courseService = $courseService;
    }
    /**
     * @OA\Get(
     *     path="/courses",
     *     summary="Get all courses",
     *     tags={"Courses"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="level",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string", enum={"beginner", "intermediate", "advanced"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of courses",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Course"))
     *     )
     * )
     */
    public function index(Request $request)
    {
        $filters = [];
        if ($request->has('status')) $filters['status'] = $request->status;
        if ($request->has('level')) $filters['level'] = $request->level;
        if ($request->has('keyword')) $filters['keyword'] = $request->keyword;

        $courses = $this->courseService->getAllCourses($filters);
        return response()->json($courses);
    }

    /**
     * @OA\Post(
     *     path="/courses",
     *     summary="Create a new course",
     *     tags={"Courses"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title","description"},
     *             @OA\Property(property="title", type="string", example="Introduction to Programming"),
     *             @OA\Property(property="description", type="string", example="Learn the basics of programming"),
     *             @OA\Property(property="thumbnail", type="string", format="url", example="https://example.com/thumbnail.jpg"),
     *             @OA\Property(property="level", type="string", enum={"BEGINNER","INTERMEDIATE","ADVANCED","EXPERT"}, example="BEGINNER"),
     *             @OA\Property(property="price", type="number", format="float", example=99.99),
     *             @OA\Property(property="duration", type="integer", example=30),
     *             @OA\Property(property="passing_score", type="integer", example=70)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Course created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Course")
     *     ),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(Request $request)
    {
        $this->authorize('create', Course::class);

        $validator = Validator::make($request->all(), [
            // Course validation
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'thumbnail'   => 'nullable|url',
            'thumbnail'   => 'nullable|url',
            'level'       => 'in:BEGINNER,INTERMEDIATE,ADVANCED,EXPERT',
            'price'       => 'nullable|numeric|min:0',
            'duration'    => 'nullable|integer|min:1',
            'passing_score' => 'integer|min:0|max:100',
            'long_description' => 'nullable|string',
            'curriculum' => 'nullable|string',
            'category' => 'nullable|string',
            'language' => 'nullable|string',
            'discount' => 'nullable|numeric|min:0',
            'tags'          => 'nullable|array',
            'tags.*'        => 'string|exists:tags,id',

            // Modules validation (optional)
            'modules' => 'nullable|array',
            'modules.*.title' => 'required|string|max:255',
            'modules.*.description' => 'nullable|string',
            'modules.*.order_index' => 'required|integer|min:0',

            // Lessons validation
            'modules.*.lessons' => 'nullable|array',
            'modules.*.lessons.*.title' => 'required|string|max:255',
            'modules.*.lessons.*.content_type' => 'required|in:VIDEO',
            'modules.*.lessons.*.content_url' => 'nullable|string', // Made nullable since video will be uploaded
            'modules.*.lessons.*.duration' => 'nullable|integer|min:1',
            'modules.*.lessons.*.order_index' => 'required|integer|min:0',
            'modules.*.lessons.*.is_free' => 'required|boolean',

            // Quizzes validation
            'modules.*.quizzes' => 'nullable|array',
            'modules.*.quizzes.*.title' => 'required|string|max:255',
            'modules.*.quizzes.*.description' => 'nullable|string',
            'modules.*.quizzes.*.quiz_type' => 'required|in:PRACTICE,GRADED,FINAL',
            'modules.*.quizzes.*.time_limit' => 'nullable|integer|min:1',
            'modules.*.quizzes.*.passing_score' => 'required|integer|min:0|max:100',
            'modules.*.quizzes.*.max_attempts' => 'nullable|integer|min:1',
            'modules.*.quizzes.*.order_index' => 'required|integer|min:0',
            'modules.*.quizzes.*.is_active' => 'required|boolean',

            // Questions validation
            'modules.*.quizzes.*.questions' => 'nullable|array',
            'modules.*.quizzes.*.questions.*.question_text' => 'required|string',
            'modules.*.quizzes.*.questions.*.question_type' => 'required|in:MULTIPLE_CHOICE,CHECKBOX,SHORT_ANSWER',
            'modules.*.quizzes.*.questions.*.points' => 'required|integer|min:1',
            'modules.*.quizzes.*.questions.*.order_index' => 'required|integer|min:0',
            'modules.*.quizzes.*.questions.*.options' => 'nullable|array',
            'modules.*.quizzes.*.questions.*.correct_answer' => 'required|string',
            'modules.*.quizzes.*.questions.*.explanation' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {
            $data = $validator->validated();
            $data['teacher_id'] = Auth::id();

            $result = $this->courseService->createCourseWithModules($data);

            return response()->json([
                'message' => 'Course created successfully',
                'course' => $result['course'],
                'thumbnail_upload_url' => $result['thumbnail_upload_url'],
                'video_upload_urls' => $result['video_upload_urls']
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create course',
                'error' => $e->getMessage()
            ], 500);
        }
    }



    /**
     * @OA\Get(
     *     path="/courses/search",
     *     summary="Search courses by name",
     *     tags={"Courses"},
     *     @OA\Parameter(
     *         name="q",
     *         in="query",
     *         required=true,
     *         description="Search query for course name",
     *         @OA\Schema(type="string", example="programming")
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Maximum number of results to return",
     *         @OA\Schema(type="integer", default=10, maximum=50)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Search results",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Course")),
     *             @OA\Property(property="total", type="integer"),
     *             @OA\Property(property="query", type="string")
     *         )
     *     )
     * )
     */
    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'q' => 'required|string|min:1|max:100',
            'limit' => 'nullable|integer|min:1|max:50'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $query = $request->input('q');
        $limit = $request->input('limit', 10);

        $result = $this->courseService->searchCourses($query, $limit);

        return response()->json($result);
    }

    /**
     * @OA\Get(
     *     path="/courses/{id}",
     *     summary="Get course details",
     *     tags={"Courses"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Course details",
     *         @OA\JsonContent(ref="#/components/schemas/Course")
     *     ),
     *     @OA\Response(response=404, description="Course not found")
     * )
     */
    public function show($id)
    {
        $course = $this->courseService->getCourseById($id);

        $ratingCounts = $course->reviews()
                           ->selectRaw('rating, count(*) as count')
                           ->groupBy('rating')
                           ->pluck('count', 'rating')
                           ->all();
        // Mặc định cho 1-5 sao nếu không có review
        $fullRatingCounts = [];
        for ($i = 1; $i <= 5; $i++) {
            $fullRatingCounts[$i] = $ratingCounts[$i] ?? 0;
        }

        return response()->json([
            'course' => $course,
            'rating_counts' => $fullRatingCounts // Gửi dữ liệu số lượng review
        ]);
    }

    /**
     * @OA\Put(
     *     path="/courses/{id}",
     *     summary="Update course",
     *     tags={"Courses"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", example="Updated Course Title"),
     *             @OA\Property(property="description", type="string", example="Updated description"),
     *             @OA\Property(property="thumbnail", type="string", format="url"),
     *             @OA\Property(property="level", type="string", enum={"BEGINNER","INTERMEDIATE","ADVANCED","EXPERT"}),
     *             @OA\Property(property="price", type="number", format="float"),
     *             @OA\Property(property="duration", type="integer"),
     *             @OA\Property(property="passing_score", type="integer"),
     *             @OA\Property(property="status", type="string", enum={"DRAFT","PUBLISHED","ARCHIVED"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Course updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Course")
     *     ),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=404, description="Course not found")
     * )
     */
    public function update(Request $request, $id)
    {
        $course = Course::findOrFail($id);

        if (Auth::id() !== $course->teacher_id && !Auth::user()->hasRole('ADMIN')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title'       => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'thumbnail'   => 'nullable|url',
            'status'      => 'in:DRAFT,PENDING,PUBLISHED,ARCHIVED',
            'level'       => 'in:BEGINNER,INTERMEDIATE,ADVANCED,EXPERT',
            'price'       => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $course = $this->courseService->updateCourse($id, $validator->validated());

        return response()->json(['message' => 'Course updated successfully', 'course' => $course]);
    }

    /**
     * @OA\Delete(
     *     path="/courses/{id}",
     *     summary="Delete course",
     *     tags={"Courses"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Course deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=404, description="Course not found")
     * )
     */
    public function destroy($id)
    {
        $course = Course::findOrFail($id);

        if (Auth::id() !== $course->teacher_id && !Auth::user()->hasRole('ADMIN')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $this->courseService->deleteCourse($id);

        return response()->json(['message' => 'Course deleted successfully']);
    }

    /**
     * @OA\Post(
     *     path="/courses/{id}/enroll",
     *     summary="Enroll in a course",
     *     description="Enroll the authenticated student in a course",
     *     operationId="enrollInCourse",
     *     tags={"Courses"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Course ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Enrollment successful",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="enrollment", ref="#/components/schemas/Enrollment")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Course not found"
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Already enrolled",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Already enrolled")
     *         )
     *     )
     * )
     */
    public function enroll($id)
    {
        $course = Course::findOrFail($id);

        $result = $this->courseService->enroll($course->id, Auth::id());

        if ($result['exists']) {
            return response()->json(['message' => 'Already enrolled'], 409);
        }

        return response()->json(['message' => 'Enrollment successful', 'enrollment' => $result['enrollment']]);
    }

    /**
     * @OA\Post(
     *     path="/courses/{id}/review",
     *     summary="Add or update course review",
     *     description="Add a review for a course or update existing review",
     *     operationId="addCourseReview",
     *     tags={"Courses"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Course ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"rating"},
     *             @OA\Property(property="rating", type="integer", minimum=1, maximum=5, example=5),
     *             @OA\Property(property="comment", type="string", nullable=true, example="Great course!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Review submitted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="review", ref="#/components/schemas/Review")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Course not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function addReview(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $course = Course::findOrFail($id);

        $review = $this->courseService->addReview(Auth::id(), $id, $request->rating, $request->comment);

        $ratingCounts = $course->reviews()
                                ->selectRaw('rating, count(*) as count')
                                ->groupBy('rating')
                                ->pluck('count', 'rating')
                                ->all();
            $fullRatingCounts = [];
            for ($i = 1; $i <= 5; $i++) {
                $fullRatingCounts[$i] = $ratingCounts[$i] ?? 0;
            }
        return response()->json([
                'message' => 'Review submitted', 
                'review' => $review->load('student'), 
                'course_stats' => [
                    'average_rating' => $course->average_rating,
                    'review_count' => $course->review_count,
                    'rating_counts' => $fullRatingCounts
                ]
            ]);
    }

    /**
     * @OA\Get(
     *     path="/courses/{id}/modules",
     *     summary="Get course modules with lessons",
     *     description="Retrieve all modules and their lessons for a specific course",
     *     operationId="getCourseModules",
     *     tags={"Courses"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Course ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of modules with lessons",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Module")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Course not found"
     *     )
     * )
     */
    public function getModulesWithLessons($id)
    {
        $modules = $this->courseService->getModulesWithLessons($id);
        return response()->json($modules);
    }

    /**
     * @OA\Get(
     *     path="/courses/{id}/students",
     *     summary="Get enrolled students",
     *     description="Get list of students enrolled in a course (teacher only)",
     *     operationId="getEnrolledStudents",
     *     tags={"Courses"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Course ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of enrolled students",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="string", format="uuid"),
     *                 @OA\Property(property="student", type="object",
     *                     @OA\Property(property="id", type="string", format="uuid"),
     *                     @OA\Property(property="first_name", type="string"),
     *                     @OA\Property(property="last_name", type="string"),
     *                     @OA\Property(property="email", type="string", format="email")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - not the course teacher"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Course not found"
     *     )
     * )
     */
    public function getEnrolledStudents($id)
    {
        $students = $this->courseService->getEnrolledStudents($id);

        return response()->json($students);
    }

    /**
     * Upload video for a lesson
     */
    public function uploadLessonVideo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lesson_id' => 'required|string|exists:lessons,id',
            'video_path' => 'required|string',
            'video_file' => 'required|file|mimes:mp4'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {
            $lessonId = $request->lesson_id;
            $videoPath = $request->video_path;
            $videoFile = $request->file('video_file');

            // Upload the video
            $this->courseService->uploadLessonVideo($videoPath, $videoFile);

            // Generate the final video URL
            $awsEndpoint = env('AWS_ENDPOINT');
            $awsBucket = env('AWS_BUCKET');
            $videoUrl = $awsEndpoint . '/' . $awsBucket . '/' . $videoPath;

            // Update lesson with the video URL
            $this->courseService->updateLessonVideo($lessonId, $videoUrl);

            return response()->json([
                'message' => 'Video uploaded successfully',
                'video_url' => $videoUrl
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to upload video',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
