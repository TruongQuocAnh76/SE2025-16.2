<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

use App\Models\User;
use App\Models\Review;
use App\Models\Certificate;
use App\Models\Enrollment;
use App\Models\QuizAttempt;

/**
 * @OA\Server(
 *     url="http://localhost:8000/api",
 *     description="Local development server"
 * )
 */
class UserController extends Controller
{
    /**
     * @OA\Get(
     *     path="/users",
     *     summary="Get all users",
     *     tags={"Users"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of users",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/User")),
     *             @OA\Property(property="current_page", type="integer"),
     *             @OA\Property(property="last_page", type="integer"),
     *             @OA\Property(property="per_page", type="integer"),
     *             @OA\Property(property="total", type="integer")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function index()
    {
        $this->authorize('viewAny', User::class);

        $users = User::select('id', 'first_name', 'last_name', 'email', 'role', 'is_active', 'created_at')
                    ->paginate(20);

        return response()->json($users);
    }

    /**
     * @OA\Get(
     *     path="/users/{id}",
     *     summary="Get user by ID",
     *     tags={"Users"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User details",
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     *     @OA\Response(response=404, description="User not found")
     * )
     */
    public function show($id)
    {
        $user = User::withCount(['reviews', 'certificates'])
                    ->findOrFail($id);

        return response()->json($user);
    }

    /**
     * @OA\Put(
     *     path="/users/{id}",
     *     summary="Update user",
     *     tags={"Users"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="first_name", type="string", example="John"),
     *             @OA\Property(property="last_name", type="string", example="Doe"),
     *             @OA\Property(property="bio", type="string", example="Bio description"),
     *             @OA\Property(property="avatar", type="string", format="url", example="https://example.com/avatar.jpg"),
     *             @OA\Property(property="password", type="string", format="password", example="newpassword")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="user", ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if (Auth::id() !== $user->id && !Auth::user()->hasRole('ADMIN')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'first_name' => 'sometimes|string|max:100',
            'last_name'  => 'sometimes|string|max:100',
            'bio'        => 'nullable|string',
            'avatar'     => 'nullable|url',
            'password'   => 'nullable|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $data = $validator->validated();

        if (isset($data['password'])) {
            // If user has a password currently (not social login only), verify current password
            if ($user->password && $user->auth_provider === 'EMAIL') {
                if (!$request->has('current_password')) {
                    return response()->json(['error' => 'Current password is required'], 422);
                }
                if (!Hash::check($request->current_password, $user->password)) {
                    return response()->json(['error' => 'Current password is incorrect'], 422);
                }
            }
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        return response()->json(['message' => 'Profile updated successfully', 'user' => $user]);
    }

    /**
     * @OA\Delete(
     *     path="/users/{id}",
     *     summary="Delete user",
     *     tags={"Users"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=404, description="User not found")
     * )
     */
    public function destroy($id)
    {
        $this->authorize('delete', User::class);

        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }
    /**
     * @OA\Get(
     *     path="/users/{id}/reviews",
     *     summary="Get user reviews",
     *     tags={"Users"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of user reviews",
     *         @OA\JsonContent(type="array", @OA\Items(type="object"))
     *     ),
     *     @OA\Response(response=404, description="User not found")
     * )
     */
    public function getUserReviews($id)
    {
        $reviews = Review::where('student_id', $id)
            ->with(['course:id,title'])
            ->orderByDesc('created_at')
            ->get();

        return response()->json($reviews);
    }

    /**
     * @OA\Get(
     *     path="/users/{id}/certificates",
     *     summary="Get user certificates",
     *     tags={"Users"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of user certificates",
     *         @OA\JsonContent(type="array", @OA\Items(type="object"))
     *     ),
     *     @OA\Response(response=404, description="User not found")
     * )
     */
    public function getUserCertificates($id)
    {
        $certs = Certificate::where('student_id', $id)
            ->with(['course:id,title'])
            ->orderByDesc('issued_at')
            ->get();

        return response()->json($certs);
    }

    /**
     * @OA\Get(
     *     path="/users/{id}/enrollments",
     *     summary="Get user enrollments",
     *     tags={"Users"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of user enrollments",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="string", format="uuid"),
     *                 @OA\Property(property="student_id", type="string", format="uuid"),
     *                 @OA\Property(property="course_id", type="string", format="uuid"),
     *                 @OA\Property(property="status", type="string", enum={"ACTIVE", "COMPLETED", "DROPPED"}),
     *                 @OA\Property(property="progress", type="number", format="decimal"),
     *                 @OA\Property(property="completed_at", type="string", format="date-time", nullable=true),
     *                 @OA\Property(property="enrolled_at", type="string", format="date-time"),
     *                 @OA\Property(property="course", ref="#/components/schemas/Course")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="User not found")
     * )
     */
    public function getUserEnrollments($id)
    {
        $user = User::findOrFail($id);

        // Get enrollments with course information
        $enrollments = Enrollment::where('student_id', $id)
            ->with(['course' => function($query) {
                $query->select('id', 'title', 'description', 'thumbnail', 'price', 'level', 'duration')
                      ->with(['teacher' => function($q) {
                          $q->select('id', 'first_name', 'last_name', 'email');
                      }]);
            }])
            ->orderByDesc('created_at')
            ->get();

        return response()->json($enrollments);
    }

    /**
     * @OA\Get(
     *     path="/users/filter",
     *     summary="Filter users by role",
     *     tags={"Users"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="role",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string", enum={"student", "instructor", "admin"}, default="student")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Filtered list of users",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/User"))
     *     )
     * )
     */
    public function filterByRole(Request $request)
    {
        $role = $request->query('role', 'STUDENT');
        $users = User::where('role', $role)
                    ->select('id', 'first_name', 'last_name', 'email', 'role')
                    ->get();

        return response()->json($users);
    }

    public function getUserQuizAttemptsCount($id)
    {
        $user = User::findOrFail($id);

        $count = QuizAttempt::where('student_id', $id)->count();

        return response()->json(['count' => $count]);
    }

    /**
     * @OA\Get(
     *     path="/users/membership-status",
     *     summary="Get current user's membership status",
     *     tags={"Users"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Membership status retrieved",
     *         @OA\JsonContent(
     *             @OA\Property(property="isPremium", type="boolean"),
     *             @OA\Property(property="membershipTier", type="string"),
     *             @OA\Property(property="expiresAt", type="string", format="date-time"),
     *             @OA\Property(property="daysRemaining", type="integer")
     *         )
     *     )
     * )
     */
    public function getMembershipStatus()
    {
        $user = Auth::user();
        
        $daysRemaining = null;
        if ($user->membership_expires_at) {
            $expiryDate = \Carbon\Carbon::parse($user->membership_expires_at);
            $daysRemaining = max(0, $expiryDate->diffInDays(now(), false) * -1);
        }

        return response()->json([
            'isPremium' => $user->isPremium(),
            'membershipTier' => $user->membership_tier,
            'expiresAt' => $user->membership_expires_at,
            'daysRemaining' => $daysRemaining
        ]);
    }
}
