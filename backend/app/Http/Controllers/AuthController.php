<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Log;

/**
 * @OA\Info(
 *     title="Learning Platform API",
 *     version="1.0.0",
 *     description="API for Learning Platform"
 * )
 * @OA\Server(
 *     url="http://localhost:8000/api",
 *     description="Local development server"
 * )
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="Token",
 *     description="Enter your Sanctum token (without 'Bearer ' prefix)"
 * )
 * @OA\Schema(
 *     schema="User",
 *     type="object",
 *     @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000"),
 *     @OA\Property(property="first_name", type="string", example="John"),
 *     @OA\Property(property="last_name", type="string", example="Doe"),
 *     @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
 *     @OA\Property(property="username", type="string", example="johndoe"),
 *     @OA\Property(property="password", type="string", format="password", nullable=true),
 *     @OA\Property(property="avatar", type="string", format="url", nullable=true, example="https://example.com/avatar.jpg"),
 *     @OA\Property(property="bio", type="string", nullable=true, example="Software developer with 5 years experience"),
 *     @OA\Property(property="role", type="string", enum={"STUDENT", "TEACHER", "ADMIN"}, example="STUDENT"),
 *     @OA\Property(property="auth_provider", type="string", enum={"EMAIL", "GOOGLE", "FACEBOOK", "GITHUB"}, example="EMAIL"),
 *     @OA\Property(property="is_email_verified", type="boolean", example=true),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 * @OA\Schema(
 *     schema="Course",
 *     type="object",
 *     @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000"),
 *     @OA\Property(property="title", type="string", example="Introduction to Programming"),
 *     @OA\Property(property="description", type="string", example="Learn the basics of programming"),
 *     @OA\Property(property="slug", type="string", example="introduction-to-programming"),
 *     @OA\Property(property="thumbnail", type="string", format="url", nullable=true, example="https://example.com/thumbnail.jpg"),
 *     @OA\Property(property="status", type="string", enum={"DRAFT", "PENDING", "PUBLISHED", "ARCHIVED"}, example="PUBLISHED"),
 *     @OA\Property(property="level", type="string", enum={"BEGINNER","INTERMEDIATE","ADVANCED","EXPERT"}, example="BEGINNER"),
 *     @OA\Property(property="price", type="number", format="decimal", nullable=true, example=99.99),
 *     @OA\Property(property="duration", type="integer", nullable=true, example=30),
 *     @OA\Property(property="passing_score", type="integer", example=70),
 *     @OA\Property(property="teacher_id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440001"),
 *     @OA\Property(property="published_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(property="teacher", ref="#/components/schemas/User", nullable=true)
 * )
 * @OA\Schema(
 *     schema="Certificate",
 *     type="object",
 *     @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000"),
 *     @OA\Property(property="certificate_number", type="string", example="CERT-2025-ABCDEF"),
 *     @OA\Property(property="student_id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440001"),
 *     @OA\Property(property="course_id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440002"),
 *     @OA\Property(property="status", type="string", enum={"PENDING", "ISSUED", "FAILED", "REVOKED"}, example="ISSUED"),
 *     @OA\Property(property="final_score", type="number", format="decimal", example=85.5),
 *     @OA\Property(property="pdf_url", type="string", nullable=true, example="https://example.com/certificates/CERT-2025-ABCDEF.pdf"),
 *     @OA\Property(property="pdf_hash", type="string", nullable=true),
 *     @OA\Property(property="issued_at", type="string", format="date-time"),
 *     @OA\Property(property="expires_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="revoked_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="revocation_reason", type="string", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(property="course", ref="#/components/schemas/Course", nullable=true),
 *     @OA\Property(property="student", ref="#/components/schemas/User", nullable=true),
 *     @OA\Property(property="blockchain_transaction", ref="#/components/schemas/BlockchainTransaction", nullable=true)
 * )
 * @OA\Schema(
 *     schema="BlockchainTransaction",
 *     type="object",
 *     @OA\Property(property="id", type="string", format="uuid"),
 *     @OA\Property(property="transaction_hash", type="string", example="0x1234567890abcdef"),
 *     @OA\Property(property="network", type="string", enum={"ETHEREUM", "POLYGON", "HYPERLEDGER"}),
 *     @OA\Property(property="status", type="string", enum={"PENDING", "CONFIRMED", "FAILED"}),
 *     @OA\Property(property="certificate_hash", type="string"),
 *     @OA\Property(property="metadata", type="object", nullable=true),
 *     @OA\Property(property="block_number", type="integer", format="int64", nullable=true),
 *     @OA\Property(property="gas_used", type="string", nullable=true),
 *     @OA\Property(property="certificate_id", type="string", format="uuid"),
 *     @OA\Property(property="confirmed_at", type="string", format="date-time", nullable=true)
 * )
 * @OA\Schema(
 *     schema="Module",
 *     type="object",
 *     @OA\Property(property="id", type="string", format="uuid"),
 *     @OA\Property(property="title", type="string"),
 *     @OA\Property(property="description", type="string", nullable=true),
 *     @OA\Property(property="order_index", type="integer"),
 *     @OA\Property(property="course_id", type="string", format="uuid"),
 *     @OA\Property(property="lessons", type="array", @OA\Items(ref="#/components/schemas/Lesson"))
 * )
 * @OA\Schema(
 *     schema="Lesson",
 *     type="object",
 *     @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000"),
 *     @OA\Property(property="title", type="string", example="Introduction to Variables"),
 *     @OA\Property(property="description", type="string", nullable=true, example="Learn about variables in programming"),
 *     @OA\Property(property="content_type", type="string", enum={"VIDEO", "PDF", "DOCUMENT", "LINK", "TEXT"}),
 *     @OA\Property(property="content_url", type="string", nullable=true, format="url"),
 *     @OA\Property(property="text_content", type="string", nullable=true),
 *     @OA\Property(property="duration", type="integer", nullable=true, example=600),
 *     @OA\Property(property="order_index", type="integer", example=1),
 *     @OA\Property(property="is_free", type="boolean", example=false),
 *     @OA\Property(property="module_id", type="string", format="uuid"),
 *     @OA\Property(property="is_completed", type="boolean", example=false),
 *     @OA\Property(property="time_spent", type="integer", example=300)
 * )
 * @OA\Schema(
 *     schema="Enrollment",
 *     type="object",
 *     @OA\Property(property="id", type="string", format="uuid"),
 *     @OA\Property(property="student_id", type="string", format="uuid"),
 *     @OA\Property(property="course_id", type="string", format="uuid"),
 *     @OA\Property(property="status", type="string", enum={"ACTIVE", "COMPLETED", "EXPIRED", "CANCELLED"}),
 *     @OA\Property(property="progress", type="integer"),
 *     @OA\Property(property="enrolled_at", type="string", format="date-time"),
 *     @OA\Property(property="completed_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="expires_at", type="string", format="date-time", nullable=true)
 * )
 * @OA\Schema(
 *     schema="Review",
 *     type="object",
 *     @OA\Property(property="id", type="string", format="uuid"),
 *     @OA\Property(property="rating", type="integer", minimum=1, maximum=5),
 *     @OA\Property(property="comment", type="string", nullable=true),
 *     @OA\Property(property="student_id", type="string", format="uuid"),
 *     @OA\Property(property="course_id", type="string", format="uuid"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 * @OA\Schema(
 *     schema="Quiz",
 *     type="object",
 *     @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000"),
 *     @OA\Property(property="title", type="string", example="Basic Programming Quiz"),
 *     @OA\Property(property="description", type="string", nullable=true, example="Test your programming knowledge"),
 *     @OA\Property(property="quiz_type", type="string", enum={"PRACTICE", "GRADED", "FINAL"}, example="GRADED"),
 *     @OA\Property(property="time_limit", type="integer", nullable=true, example=30),
 *     @OA\Property(property="passing_score", type="integer", example=70),
 *     @OA\Property(property="max_attempts", type="integer", nullable=true, example=3),
 *     @OA\Property(property="order_index", type="integer", example=1),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="course_id", type="string", format="uuid"),
 *     @OA\Property(property="questions", type="array", @OA\Items(ref="#/components/schemas/Question"))
 * )
 * @OA\Schema(
 *     schema="Question",
 *     type="object",
 *     @OA\Property(property="id", type="string", format="uuid"),
 *     @OA\Property(property="question_text", type="string"),
 *     @OA\Property(property="question_type", type="string", enum={"MULTIPLE_CHOICE", "TRUE_FALSE", "SHORT_ANSWER", "ESSAY"}),
 *     @OA\Property(property="points", type="integer", default=1),
 *     @OA\Property(property="order_index", type="integer"),
 *     @OA\Property(property="options", type="array", @OA\Items(type="string"), nullable=true),
 *     @OA\Property(property="correct_answer", type="string"),
 *     @OA\Property(property="explanation", type="string", nullable=true)
 * )
 * @OA\Schema(
 *     schema="QuizAttempt",
 *     type="object",
 *     @OA\Property(property="id", type="string", format="uuid"),
 *     @OA\Property(property="student_id", type="string", format="uuid"),
 *     @OA\Property(property="quiz_id", type="string", format="uuid"),
 *     @OA\Property(property="score", type="number", format="decimal", nullable=true),
 *     @OA\Property(property="is_passed", type="boolean"),
 *     @OA\Property(property="attempt_number", type="integer"),
 *     @OA\Property(property="time_spent", type="integer", nullable=true),
 *     @OA\Property(property="started_at", type="string", format="date-time"),
 *     @OA\Property(property="submitted_at", type="string", format="date-time", nullable=true)
 * )
 * @OA\Schema(
 *     schema="Progress",
 *     type="object",
 *     @OA\Property(property="id", type="string", format="uuid"),
 *     @OA\Property(property="student_id", type="string", format="uuid"),
 *     @OA\Property(property="lesson_id", type="string", format="uuid"),
 *     @OA\Property(property="is_completed", type="boolean"),
 *     @OA\Property(property="time_spent", type="integer"),
 *     @OA\Property(property="last_accessed_at", type="string", format="date-time"),
 *     @OA\Property(property="completed_at", type="string", format="date-time", nullable=true)
 * )
 * @OA\Schema(
 *     schema="SystemLog",
 *     type="object",
 *     @OA\Property(property="id", type="string", format="uuid"),
 *     @OA\Property(property="level", type="string", enum={"INFO", "WARNING", "ERROR", "CRITICAL"}),
 *     @OA\Property(property="message", type="string"),
 *     @OA\Property(property="context", type="object", nullable=true),
 *     @OA\Property(property="user_id", type="string", format="uuid", nullable=true),
 *     @OA\Property(property="ip_address", type="string", nullable=true),
 *     @OA\Property(property="user_agent", type="string", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time")
 * )
 */
class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/auth/register",
     *     summary="Register a new user",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"username","email","password","first_name","last_name"},
     *             @OA\Property(property="username", type="string", description="Username", example="johndoe"),
     *             @OA\Property(property="email", type="string", format="email", description="Email address", example="user@example.com"),
     *             @OA\Property(property="password", type="string", format="password", minLength=6, description="Password (min 6 characters)", example="password123"),
     *             @OA\Property(property="first_name", type="string", description="First name", example="John"),
     *             @OA\Property(property="last_name", type="string", description="Last name", example="Doe")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User registered successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="user", ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'username' => [
                'required',
                'string',
                'min:3',
                'max:20',
                'regex:/^[a-zA-Z0-9_]+$/',
                'unique:users,username'
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email'
            ],
            'password' => [
                'required',
                'string',
                'min:6',
                'max:100'
            ],
            'first_name' => [
                'required',
                'string',
                'min:2',
                'max:50'
            ],
            'last_name' => [
                'required',
                'string',
                'min:2',
                'max:50'
            ],
        ], [
            'username.required' => 'Username is required',
            'username.min' => 'Username must be at least 3 characters',
            'username.max' => 'Username must not exceed 20 characters',
            'username.regex' => 'Username can only contain letters, numbers, and underscores',
            'username.unique' => 'This username is already taken',
            'email.required' => 'Email is required',
            'email.email' => 'Please enter a valid email address',
            'email.unique' => 'This email is already registered',
            'password.required' => 'Password is required',
            'password.min' => 'Password must be at least 6 characters',
            'password.max' => 'Password is too long',
            'first_name.required' => 'First name is required',
            'first_name.min' => 'First name must be at least 2 characters',
            'first_name.max' => 'First name must not exceed 50 characters',
            'last_name.required' => 'Last name is required',
            'last_name.min' => 'Last name must be at least 2 characters',
            'last_name.max' => 'Last name must not exceed 50 characters',
        ]);

        $user = User::create([
            'id' => Str::uuid(),
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'auth_provider' => 'EMAIL',
            'role' => 'STUDENT',
        ]);

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user,
        ], 201);
    }

    /**
     * @OA\Post(
     *     path="/auth/login",
     *     summary="Login user",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"login","password"},
     *             @OA\Property(property="login", type="string", description="Email or username", example="user@example.com"),
     *             @OA\Property(property="password", type="string", format="password", description="Password (min 6 characters)", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="user", ref="#/components/schemas/User"),
     *             @OA\Property(property="token", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Invalid email or password."),
     *             @OA\Property(property="error", type="string", example="authentication_failed")
     *         )
     *     )
     * )
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'login' => [
                'required',
                'string'
            ],
            'password' => [
                'required',
                'string',
                'min:6'
            ]
        ], [
            'login.required' => 'Username or email is required',
            'password.required' => 'Password is required',
            'password.min' => 'Password must be at least 6 characters',
        ]);

        $user = User::where('email', $validated['login'])
            ->orWhere('username', $validated['login'])
            ->first();

        if (!$user) {
            return response()->json([
                'message' => 'Invalid credentials. Please check your username/email and password.',
                'error' => 'authentication_failed'
            ], 401);
        }

        if (!Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials. Please check your username/email and password.',
                'error' => 'authentication_failed'
            ], 401);
        }

        // Check if user is active
        if (!$user->is_active) {
            return response()->json([
                'message' => 'Your account has been deactivated. Please contact support.',
                'error' => 'account_deactivated'
            ], 403);
        }

        // Tạo token (Laravel Sanctum)
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ]);
    }

    /**
     * @OA\Post(
     *     path="/auth/logout",
     *     summary="Logout user",
     *     tags={"Authentication"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Logged out successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function logout(Request $request)
    {
        if ($request->user()) {
            $request->user()->currentAccessToken()->delete();
        }

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }

    /**
     * @OA\Get(
     *     path="/auth/me",
     *     summary="Get current user info",
     *     tags={"Authentication"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Current user information",
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    /**
     * @OA\Get(
     *     path="/auth/google",
     *     summary="Redirect to Google OAuth",
     *     tags={"Authentication"},
     *     @OA\Response(
     *         response=302,
     *         description="Redirect to Google OAuth"
     *     )
     * )
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * @OA\Get(
     *     path="/auth/google/callback",
     *     summary="Handle Google OAuth callback",
     *     tags={"Authentication"},
     *     @OA\Response(
     *         response=302,
     *         description="Redirect to frontend with token"
     *     )
     * )
     */
    public function handleGoogleCallback()
    {
        try {
            $socialiteUser = Socialite::driver('google')->user();

            $user = User::firstOrCreate(
                ['email' => $socialiteUser->email],
                [
                    'id' => Str::uuid(),
                    'google_id' => $socialiteUser->id,
                    'first_name' => $socialiteUser->user['given_name'] ?? $socialiteUser->name,
                    'last_name' => $socialiteUser->user['family_name'] ?? '',
                    'username' => $socialiteUser->email,
                    'avatar' => $socialiteUser->avatar,
                    'auth_provider' => 'GOOGLE',
                    'is_email_verified' => true,
                    'role' => 'STUDENT',
                ]
            );

            // Update google_id if user exists but doesn't have it
            if (!$user->google_id) {
                $user->google_id = $socialiteUser->id;
                $user->avatar = $socialiteUser->avatar;
                $user->save();
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return redirect(env('FRONTEND_URL', 'http://localhost:3000') . '/auth/oauth-callback?token=' . $token . '&user=' . urlencode(json_encode([
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'username' => $user->username,
                'avatar' => $user->avatar,
                'role' => $user->role,
                'auth_provider' => $user->auth_provider,
            ])));
        } catch (\Exception $e) {
            Log::error('Google OAuth error: ' . $e->getMessage());
            return redirect(env('FRONTEND_URL', 'http://localhost:3000') . '/auth/login?error=' . urlencode('Google authentication failed'));
        }
    }

    /**
     * @OA\Get(
     *     path="/auth/facebook",
     *     summary="Redirect to Facebook OAuth",
     *     tags={"Authentication"},
     *     @OA\Response(
     *         response=302,
     *         description="Redirect to Facebook OAuth"
     *     )
     * )
     */
    public function redirectToFacebook()
    {
        return Socialite::driver('facebook')->redirect();
    }

    /**
     * @OA\Get(
     *     path="/auth/facebook/callback",
     *     summary="Handle Facebook OAuth callback",
     *     tags={"Authentication"},
     *     @OA\Response(
     *         response=302,
     *         description="Redirect to frontend with token"
     *     )
     * )
     */
    public function handleFacebookCallback()
    {
        try {
            $socialiteUser = Socialite::driver('facebook')->user();

            $user = User::firstOrCreate(
                ['email' => $socialiteUser->email],
                [
                    'id' => Str::uuid(),
                    'facebook_id' => $socialiteUser->id,
                    'first_name' => explode(' ', $socialiteUser->name)[0] ?? $socialiteUser->name,
                    'last_name' => explode(' ', $socialiteUser->name, 2)[1] ?? '',
                    'username' => $socialiteUser->email,
                    'avatar' => $socialiteUser->avatar,
                    'auth_provider' => 'FACEBOOK',
                    'is_email_verified' => true,
                    'role' => 'STUDENT',
                ]
            );

            // Update facebook_id if user exists but doesn't have it
            if (!$user->facebook_id) {
                $user->facebook_id = $socialiteUser->id;
                $user->avatar = $socialiteUser->avatar;
                $user->save();
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return redirect(env('FRONTEND_URL', 'http://localhost:3000') . '/auth/oauth-callback?token=' . $token . '&user=' . urlencode(json_encode([
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'username' => $user->username,
                'avatar' => $user->avatar,
                'role' => $user->role,
                'auth_provider' => $user->auth_provider,
            ])));
        } catch (\Exception $e) {
            Log::error('Facebook OAuth error: ' . $e->getMessage());
            return redirect(env('FRONTEND_URL', 'http://localhost:3000') . '/auth/login?error=' . urlencode('Facebook authentication failed'));
        }
    }
}
