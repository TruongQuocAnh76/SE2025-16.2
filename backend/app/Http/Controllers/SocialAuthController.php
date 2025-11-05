<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    /**
     * Redirect to Google OAuth
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    /**
     * Handle Google OAuth Callback
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
            
            // Create username from email if not exists
            $username = explode('@', $googleUser->getEmail())[0];
            
            $user = User::firstOrCreate(
                ['email' => $googleUser->getEmail()],
                [
                    'id' => (string) Str::uuid(),
                    'first_name' => $googleUser->user['given_name'] ?? '',
                    'last_name' => $googleUser->user['family_name'] ?? '',
                    'email' => $googleUser->getEmail(),
                    'username' => $username, // Add username from email
                    'google_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                    'auth_provider' => 'GOOGLE',
                    'role' => 'STUDENT', // default role
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );

            // Update google_id if user exists but doesn't have it
            if (!$user->google_id) {
                $user->update([
                    'google_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                ]);
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            // Redirect to frontend with token
            return redirect(env('FRONTEND_URL', 'http://localhost:3000') . '/auth/oauth-callback?access_token=' . $token . '&user=' . urlencode(json_encode($user)));
            
        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('Google OAuth failed: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return redirect(env('FRONTEND_URL', 'http://localhost:3000') . '/auth/login?error=' . urlencode('Google authentication failed'));
        }
    }

    /**
     * Redirect to Facebook OAuth
     */
    public function redirectToFacebook()
    {
        return Socialite::driver('facebook')->stateless()->redirect();
    }

    /**
     * Handle Facebook OAuth Callback
     */
    public function handleFacebookCallback()
    {
        try {
            $facebookUser = Socialite::driver('facebook')->stateless()->user();
            
            $nameParts = explode(' ', $facebookUser->getName());
            $firstName = $nameParts[0] ?? '';
            $lastName = isset($nameParts[1]) ? implode(' ', array_slice($nameParts, 1)) : '';
            
            // Create username from email if not exists
            $username = explode('@', $facebookUser->getEmail())[0];
            
            $user = User::firstOrCreate(
                ['email' => $facebookUser->getEmail()],
                [
                    'id' => (string) Str::uuid(),
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $facebookUser->getEmail(),
                    'username' => $username, // Add username from email
                    'facebook_id' => $facebookUser->getId(),
                    'avatar' => $facebookUser->getAvatar(),
                    'auth_provider' => 'FACEBOOK',
                    'role' => 'STUDENT', // default role
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );

            // Update facebook_id if user exists but doesn't have it
            if (!$user->facebook_id) {
                $user->update([
                    'facebook_id' => $facebookUser->getId(),
                    'avatar' => $facebookUser->getAvatar(),
                ]);
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            // Redirect to frontend with token
            return redirect(env('FRONTEND_URL', 'http://localhost:3000') . '/auth/oauth-callback?access_token=' . $token . '&user=' . urlencode(json_encode($user)));
            
        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('Facebook OAuth failed: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return redirect(env('FRONTEND_URL', 'http://localhost:3000') . '/auth/login?error=' . urlencode('Facebook authentication failed'));
        }
    }
}
