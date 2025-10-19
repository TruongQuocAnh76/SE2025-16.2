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

class CourseController extends Controller
{
    /**
     * 📚 Danh sách tất cả khoá học (có filter)
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
     * ➕ Tạo mới khoá học (TEACHER hoặc ADMIN)
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
     * 🔍 Xem chi tiết khoá học + module + lesson
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
     * ✏️ Cập nhật thông tin khoá học
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
     * ❌ Xoá khoá học
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
