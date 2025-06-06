<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserService
{
  public function getUsers(array $params): LengthAwarePaginator
  {
    $query = User::query();

    if (!empty($params['search'])) {
      $query->where('name', 'like', '%' . $params['search'] . '%')
        ->orWhere('email', 'like', '%' . $params['search'] . '%');
    }

    return $query->orderBy($params['sort_by'], $params['sort_order'])
      ->paginate($params['per_page']);
  }

  public function getUserById(string $id): User
  {
    return User::findOrFail($id);
  }

  public function createUser(array $data): User
  {
    $data['password'] = bcrypt($data['password']);
    return User::create($data);
  }

  public function updateUser(string $id, array $data): User
  {
    $user = $this->getUserById($id);
    if (isset($data['password'])) {
      $data['password'] = bcrypt($data['password']);
    }
    $user->update($data);
    return $user;
  }

  public function deleteUser(string $id): void
  {
    $user = $this->getUserById($id);
    $user->delete();
  }
}