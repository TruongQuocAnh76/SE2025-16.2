<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Course;
use App\Models\Enrollment;

/**
 * @OA\Server(
 *     url="http://localhost:8000/api",
 *     description="Local development server"
 * )
 */
class TeacherController extends Controller
{
    /**
     * @OA\Get(
     *     path="/teachers/{id}/courses",
     *     summary="Get all courses taught by a specific teacher",
     *     description="Retrieve all courses created by a specific teacher with enrollment counts",
     *     operationId="getTeacherCourses",
     *     tags={"Teachers"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Teacher ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         required=false,
     *         description="Filter by course status",
     *         @OA\Schema(type="string", enum={"DRAFT", "PENDING", "PUBLISHED", "ARCHIVED"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of teacher's courses",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="teacher", type="object",
     *                 @OA\Property(property="id", type="string", format="uuid"),
     *                 @OA\Property(property="first_name", type="string"),
     *                 @OA\Property(property="last_name", type="string"),
     *                 @OA\Property(property="email", type="string", format="email")
     *             ),
     *             @OA\Property(property="courses", type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="string", format="uuid"),
     *                     @OA\Property(property="title", type="string"),
     *                     @OA\Property(property="slug", type="string"),
     *                     @OA\Property(property="status", type="string"),
     *                     @OA\Property(property="level", type="string"),
     *                     @OA\Property(property="price", type="number", format="float"),
     *                     @OA\Property(property="enrollments_count", type="integer"),
     *                     @OA\Property(property="reviews_avg_rating", type="number", format="float"),
     *                     @OA\Property(property="created_at", type="string", format="date-time")
     *                 )
     *             ),
     *             @OA\Property(property="total_courses", type="integer"),
     *             @OA\Property(property="total_enrollments", type="integer")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Teacher not found")
     * )
     */
    public function getCourses($id, Request $request)
    {
        $teacher = User::findOrFail($id);

        // Verify the user is actually a teacher
        if ($teacher->role !== 'TEACHER' && $teacher->role !== 'ADMIN') {
            return response()->json([
                'error' => 'User is not a teacher'
            ], 400);
        }

        $query = Course::where('teacher_id', $id)
            ->withCount('enrollments')
            ->withAvg('reviews', 'rating');

        // Apply status filter if provided
        if ($request->has('status')) {
            $validator = Validator::make($request->all(), [
                'status' => 'in:DRAFT,PENDING,PUBLISHED,ARCHIVED'
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            $query->where('status', $request->status);
        }

        $courses = $query->orderByDesc('created_at')->get([
            'id', 
            'title', 
            'slug', 
            'description',
            'thumbnail',
            'status', 
            'level', 
            'price',
            'duration',
            'passing_score',
            'created_at',
            'published_at'
        ]);

        $totalEnrollments = $courses->sum('enrollments_count');

        return response()->json([
            'teacher' => [
                'id' => $teacher->id,
                'first_name' => $teacher->first_name,
                'last_name' => $teacher->last_name,
                'email' => $teacher->email
            ],
            'courses' => $courses,
            'total_courses' => $courses->count(),
            'total_enrollments' => $totalEnrollments
        ]);
    }

    /**
     * @OA\Get(
     *     path="/teachers/{id}/students",
     *     summary="Get all students enrolled in a teacher's courses",
     *     description="Retrieve unique students enrolled in any course taught by the specified teacher, with enrollment details",
     *     operationId="getTeacherStudents",
     *     tags={"Teachers"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Teacher ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Parameter(
     *         name="course_id",
     *         in="query",
     *         required=false,
     *         description="Filter students by specific course ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of students",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="teacher", type="object",
     *                 @OA\Property(property="id", type="string", format="uuid"),
     *                 @OA\Property(property="first_name", type="string"),
     *                 @OA\Property(property="last_name", type="string"),
     *                 @OA\Property(property="email", type="string", format="email")
     *             ),
     *             @OA\Property(property="students", type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="student_id", type="string", format="uuid"),
     *                     @OA\Property(property="first_name", type="string"),
     *                     @OA\Property(property="last_name", type="string"),
     *                     @OA\Property(property="email", type="string", format="email"),
     *                     @OA\Property(property="enrollments", type="array",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(property="enrollment_id", type="string", format="uuid"),
     *                             @OA\Property(property="course_id", type="string", format="uuid"),
     *                             @OA\Property(property="course_title", type="string"),
     *                             @OA\Property(property="status", type="string"),
     *                             @OA\Property(property="progress", type="number", format="decimal"),
     *                             @OA\Property(property="enrolled_at", type="string", format="date-time"),
     *                             @OA\Property(property="completed_at", type="string", format="date-time", nullable=true)
     *                         )
     *                     ),
     *                     @OA\Property(property="total_courses_enrolled", type="integer")
     *                 )
     *             ),
     *             @OA\Property(property="total_students", type="integer"),
     *             @OA\Property(property="total_enrollments", type="integer")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Teacher not found")
     * )
     */
    public function getStudents($id, Request $request)
    {
        $teacher = User::findOrFail($id);

        // Verify the user is actually a teacher
        if ($teacher->role !== 'TEACHER' && $teacher->role !== 'ADMIN') {
            return response()->json([
                'error' => 'User is not a teacher'
            ], 400);
        }

        // Build query for enrollments in teacher's courses
        $enrollmentsQuery = Enrollment::whereHas('course', function ($query) use ($id) {
            $query->where('teacher_id', $id);
        });

        // Apply course filter if provided
        if ($request->has('course_id')) {
            $validator = Validator::make($request->all(), [
                'course_id' => 'exists:courses,id'
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            // Verify the course belongs to this teacher
            $course = Course::findOrFail($request->course_id);
            if ($course->teacher_id !== $id) {
                return response()->json([
                    'error' => 'Course does not belong to this teacher'
                ], 403);
            }

            $enrollmentsQuery->where('course_id', $request->course_id);
        }

        $enrollments = $enrollmentsQuery
            ->with([
                'student:id,first_name,last_name,email',
                'course:id,title,teacher_id'
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        // Group enrollments by student
        $studentsMap = [];
        foreach ($enrollments as $enrollment) {
            $studentId = $enrollment->student_id;
            
            if (!isset($studentsMap[$studentId])) {
                $studentsMap[$studentId] = [
                    'student_id' => $enrollment->student->id,
                    'first_name' => $enrollment->student->first_name,
                    'last_name' => $enrollment->student->last_name,
                    'email' => $enrollment->student->email,
                    'enrollments' => [],
                    'total_courses_enrolled' => 0
                ];
            }

            $studentsMap[$studentId]['enrollments'][] = [
                'enrollment_id' => $enrollment->id,
                'course_id' => $enrollment->course_id,
                'course_title' => $enrollment->course->title,
                'status' => $enrollment->status,
                'progress' => $enrollment->progress,
                'enrolled_at' => $enrollment->created_at,
                'completed_at' => $enrollment->completed_at
            ];
            $studentsMap[$studentId]['total_courses_enrolled']++;
        }

        $students = array_values($studentsMap);

        return response()->json([
            'teacher' => [
                'id' => $teacher->id,
                'first_name' => $teacher->first_name,
                'last_name' => $teacher->last_name,
                'email' => $teacher->email
            ],
            'students' => $students,
            'total_students' => count($students),
            'total_enrollments' => $enrollments->count()
        ]);
    }

    /**
     * @OA\Get(
     *     path="/teachers/{id}/statistics",
     *     summary="Get teacher statistics",
     *     description="Get aggregated statistics for a teacher including course counts, enrollment counts, and average ratings",
     *     operationId="getTeacherStatistics",
     *     tags={"Teachers"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Teacher ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Teacher statistics",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="teacher", type="object",
     *                 @OA\Property(property="id", type="string", format="uuid"),
     *                 @OA\Property(property="first_name", type="string"),
     *                 @OA\Property(property="last_name", type="string"),
     *                 @OA\Property(property="email", type="string", format="email")
     *             ),
     *             @OA\Property(property="statistics", type="object",
     *                 @OA\Property(property="total_courses", type="integer"),
     *                 @OA\Property(property="published_courses", type="integer"),
     *                 @OA\Property(property="draft_courses", type="integer"),
     *                 @OA\Property(property="total_enrollments", type="integer"),
     *                 @OA\Property(property="unique_students", type="integer"),
     *                 @OA\Property(property="average_rating", type="number", format="float"),
     *                 @OA\Property(property="total_reviews", type="integer")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Teacher not found")
     * )
     */
    public function getStatistics($id)
    {
        $teacher = User::findOrFail($id);

        // Verify the user is actually a teacher
        if ($teacher->role !== 'TEACHER' && $teacher->role !== 'ADMIN') {
            return response()->json([
                'error' => 'User is not a teacher'
            ], 400);
        }

        $courses = Course::where('teacher_id', $id)->get();

        $totalCourses = $courses->count();
        $publishedCourses = $courses->where('status', 'PUBLISHED')->count();
        $draftCourses = $courses->where('status', 'DRAFT')->count();

        // Get total enrollments across all courses
        $totalEnrollments = Enrollment::whereIn('course_id', $courses->pluck('id'))->count();

        // Get unique students
        $uniqueStudents = Enrollment::whereIn('course_id', $courses->pluck('id'))
            ->distinct('student_id')
            ->count('student_id');

        // Get average rating and total reviews
        $courseIds = $courses->pluck('id');
        $reviews = \App\Models\Review::whereIn('course_id', $courseIds)->get();
        $totalReviews = $reviews->count();
        $averageRating = $totalReviews > 0 ? round($reviews->avg('rating'), 2) : 0;

        return response()->json([
            'teacher' => [
                'id' => $teacher->id,
                'first_name' => $teacher->first_name,
                'last_name' => $teacher->last_name,
                'email' => $teacher->email
            ],
            'statistics' => [
                'total_courses' => $totalCourses,
                'published_courses' => $publishedCourses,
                'draft_courses' => $draftCourses,
                'total_enrollments' => $totalEnrollments,
                'unique_students' => $uniqueStudents,
                'average_rating' => $averageRating,
                'total_reviews' => $totalReviews
            ]
        ]);
    }
}
