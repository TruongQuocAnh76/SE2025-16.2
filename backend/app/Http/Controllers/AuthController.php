<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Validation\ValidationException;

/**
 * @OA\Server(
 *     url="http://localhost:8000/api",
 *     description="Local development server"
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
     *             required={"email","password","first_name","last_name"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="password", type="string", format="password", minLength=6, example="password123"),
     *             @OA\Property(property="first_name", type="string", example="John"),
     *             @OA\Property(property="last_name", type="string", example="Doe")
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
        $request->validate([
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
        ]);

        $user = User::create([
            'id' => Str::uuid(),
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
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
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123")
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
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid email or password.',
                'error' => 'authentication_failed'
            ], 401);
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
        $request->user()->currentAccessToken()->delete();

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
}
