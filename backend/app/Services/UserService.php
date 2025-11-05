<?php namespace App\Services;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserService {
    protected $userRepository;
    public function __construct(UserRepository $userRepository) { $this->userRepository = $userRepository; }
    public function getAllUsers($perPage = 20) {
        return $this->userRepository->getAll($perPage);
    }
    public function getUserById($id) {
        return $this->userRepository->getById($id);
    }
    public function updateUserProfile($userId, array $data) {
        if (isset($data['password'])) $data['password'] = Hash::make($data['password']);
        return $this->userRepository->update($userId, $data);
    }
    public function deleteUser($userId) {
        return $this->userRepository->delete($userId);
    }
    public function getUsersByRole($role) {
        return $this->userRepository->getByRole($role);
    }
    public function getUserReviews($userId) {
        $user = $this->userRepository->getById($userId);
        return $user->reviews()->with('course:id,title')->orderByDesc('created_at')->get();
    }
    public function getUserCertificates($userId) {
        $user = $this->userRepository->getById($userId);
        return $user->certificates()->with('course:id,title')->orderByDesc('issued_at')->get();
    }
}