<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

use App\Models\User;
use App\Models\Review;
use App\Models\Certificate;

class UserController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', User::class);

        $users = User::select('id', 'first_name', 'last_name', 'email', 'role', 'is_active', 'created_at')
                    ->paginate(20);

        return response()->json($users);
    }

    public function show($id)
    {
        $user = User::withCount(['reviews', 'certificates'])
                    ->findOrFail($id);

        return response()->json($user);
    }

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
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        return response()->json(['message' => 'Profile updated successfully', 'user' => $user]);
    }

    public function destroy($id)
    {
        $this->authorize('delete', User::class);

        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }
    public function getUserReviews($id)
    {
        $reviews = Review::where('student_id', $id)
            ->with(['course:id,title'])
            ->orderByDesc('created_at')
            ->get();

        return response()->json($reviews);
    }

    public function getUserCertificates($id)
    {
        $certs = Certificate::where('student_id', $id)
            ->with(['course:id,title'])
            ->orderByDesc('issued_at')
            ->get();

        return response()->json($certs);
    }

    public function filterByRole(Request $request)
    {
        $role = $request->query('role', 'STUDENT');
        $users = User::where('role', $role)
                    ->select('id', 'first_name', 'last_name', 'email', 'role')
                    ->get();

        return response()->json($users);
    }
}
