<?php namespace App\Repositories;
use App\Models\User;

class UserRepository {
    protected $model;
    public function __construct(User $model) { $this->model = $model; }
    public function getAll($perPage = 20) {
        return $this->model->select('id', 'first_name', 'last_name', 'email', 'role', 'is_active', 'created_at')->paginate($perPage);
    }
    public function getById($id) {
        return $this->model->withCount(['reviews', 'certificates'])->findOrFail($id);
    }
    public function getByEmail($email) {
        return $this->model->where('email', $email)->first();
    }
    public function create(array $data) {
        return $this->model->create($data);
    }
    public function update($id, array $data) {
        $user = $this->model->findOrFail($id);
        $user->update($data);
        return $user;
    }
    public function delete($id) {
        return $this->model->findOrFail($id)->delete();
    }
    public function getByRole($role) {
        return $this->model->where('role', $role)->select('id', 'first_name', 'last_name', 'email', 'role')->get();
    }
}