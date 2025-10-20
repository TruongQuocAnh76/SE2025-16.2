<?php namespace App\Services;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthService {
    protected $userRepository;
    public function __construct(UserRepository $userRepository) { $this->userRepository = $userRepository; }
    public function register(array $data) {
        $data['id'] = Str::uuid();
        $data['password'] = Hash::make($data['password']);
        $data['auth_provider'] = 'EMAIL';
        $data['role'] = 'STUDENT';
        return $this->userRepository->create($data);
    }
    public function login($email, $password) {
        $user = $this->userRepository->getByEmail($email);
        if (!$user || !Hash::check($password, $user->password)) {
            throw ValidationException::withMessages(['email' => ['Invalid credentials']]);
        }
        $token = $user->createToken('auth_token')->plainTextToken;
        return ['user' => $user, 'token' => $token];
    }
    public function logout($user) {
        $user->currentAccessToken()->delete();
    }
}