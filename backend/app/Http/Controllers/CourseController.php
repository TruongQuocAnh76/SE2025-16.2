<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

use App\Models\Course;
use App\Models\Module;
use App\Models\Lesson;
use App\Models\Enrollment;
use App\Models\Review;
use App\Models\User;

/**
 * @OA\Server(
 *     url="http://localhost:8000/api",
 *     description="Local development server"
 * )
 * @OA\Schema(
 *     schema="Course",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="title", type="string", example="Introduction to Programming"),
 *     @OA\Property(property="description", type="string", example="Learn the basics of programming"),
 *     @OA\Property(property="price", type="number", format="float", example=99.99),
 *     @OA\Property(property="level", type="string", enum={"beginner", "intermediate", "advanced"}, example="beginner"),
 *     @OA\Property(property="category", type="string", example="Programming"),
 *     @OA\Property(property="instructor_id", type="integer", example=1),
 *     @OA\Property(property="created_at", type="string", format="date-time")
 * )
 */
class CourseController extends Controller
{
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
        $query = Course::with('teacher:id,first_name,last_name');

        // Optional filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('level')) {
            $query->where('level', $request->level);
        }

        if ($request->has('keyword')) {
            $keyword = $request->keyword;
            $query->where('title', 'like', "%$keyword%");
        }

        $courses = $query->orderByDesc('created_at')->paginate(12);

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
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'thumbnail'   => 'nullable|url',
            'level'       => 'in:BEGINNER,INTERMEDIATE,ADVANCED,EXPERT',
            'price'       => 'nullable|numeric|min:0',
            'duration'    => 'nullable|integer|min:1',
            'passing_score' => 'integer|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $data = $validator->validated();
        $data['id'] = Str::uuid();
        $data['slug'] = Str::slug($data['title']).'-'.Str::random(5);
        $data['teacher_id'] = Auth::id();
        $data['status'] = 'DRAFT';

        $course = Course::create($data);

        return response()->json(['message' => 'Course created successfully', 'course' => $course]);
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
        $course = Course::with([
            'teacher:id,first_name,last_name,email',
            'modules.lessons:id,title,order_index,module_id,is_free',
            'reviews.student:id,first_name,last_name'
        ])->findOrFail($id);

        return response()->json($course);
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

        $course->update($validator->validated());

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

        $course->delete();

        return response()->json(['message' => 'Course deleted successfully']);
    }

    /**
     * 🧑‍🎓 Đăng ký khoá học (Student)
     */
    public function enroll($id)
    {
        $course = Course::findOrFail($id);

        $exists = Enrollment::where('student_id', Auth::id())
                            ->where('course_id', $course->id)
                            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Already enrolled'], 409);
        }

        $enrollment = Enrollment::create([
            'id' => Str::uuid(),
            'student_id' => Auth::id(),
            'course_id' => $course->id,
            'status' => 'ACTIVE',
            'progress' => 0,
        ]);

        return response()->json(['message' => 'Enrollment successful', 'enrollment' => $enrollment]);
    }

    /**
     * ⭐ Thêm hoặc cập nhật review khoá học
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

        $review = Review::updateOrCreate(
            [
                'student_id' => Auth::id(),
                'course_id' => $id,
            ],
            [
                'rating' => $request->rating,
                'comment' => $request->comment,
            ]
        );

        return response()->json(['message' => 'Review submitted', 'review' => $review]);
    }

    /**
     * 🧩 Lấy danh sách module + lesson của khoá học
     */
    public function getModulesWithLessons($id)
    {
        $modules = Module::where('course_id', $id)
                    ->with(['lessons' => function ($query) {
                        $query->orderBy('order_index');
                    }])
                    ->orderBy('order_index')
                    ->get();

        return response()->json($modules);
    }

    /**
     * 🧮 Lấy danh sách học viên của khoá học
     */
    public function getEnrolledStudents($id)
    {
        $students = Enrollment::where('course_id', $id)
                    ->with(['student:id,first_name,last_name,email'])
                    ->get();

        return response()->json($students);
    }
}
