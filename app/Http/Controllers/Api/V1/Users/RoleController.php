<?php

namespace App\Http\Controllers\Api\V1\Users;

use App\Http\Controllers\Api\V1\BaseController;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;


class RoleController extends BaseController implements HasMiddleware
{
  public static function middleware(): array
  {
    return [
      new Middleware('permission:roles-access', only: ['index', 'show']),
      new Middleware('permission:roles-create', only: ['store']),
      new Middleware('permission:roles-update', only: ['update']),
      new Middleware('permission:roles-delete', only: ['destroy']),
    ];
  }

  public function index()
  {
    $roles = Role::all();

    return $this->sendSuccess(200, $roles->toArray(), 'ROLES_RETRIEVED_SUCCESSFULLY');
  }

  /**
   * Show the form for creating a new resource.
   */
  public function create()
  {
    //
  }

  public function store(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'name' => 'required|string|max:255|unique:roles',
    ]);

    if ($validator->fails()) {
      return $this->sendError(422, 'error', $validator->errors()->all());
    }

    $role = Role::create($request->all());

    return $this->sendSuccess(200, $role->toArray(), 'ROLE_CREATED_SUCCESSFULLY');
  }

  public function show(string $id)
  {
    $role = Role::find($id);

    if (!$role) {
      return $this->sendError(404, 'ROLE_NOT_FOUND');
    }

    return $this->sendSuccess(200, $role, 'ROLE_RETRIEVED_SUCCESSFULLY');
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit(string $id)
  {
    //
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, string $id)
  {
    $validator = Validator::make($request->all(), [
      "name" => "required|unique:permissions,name," . $id,
    ]);

    if ($validator->fails()) {
      return $this->sendError(422, 'error', $validator->errors()->all());
    }

    $role = Role::find($id);

    if (!$role) {
      return $this->sendError(404, 'ROLE_NOT_FOUND');
    }

    $role->update([
      "name" => $request->name,
    ]);

    return $this->sendSuccess(200, $role, 'ROLE_UPDATED_SUCCESSFULLY');
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(string $id)
  {
    try {

      $role = Role::find($id);

      if (!$role) {
        return $this->sendError(404, 'ROLE_NOT_FOUND');
      }

      // Check if the role is assigned to any user
      if ($role->users()->count() > 0) {
        return $this->sendError(422, 'ROLE_CANNOT_BE_DELETED', ['This role is assigned to one or more users.']);
      }

      // Delete role
      $role->delete();

      return $this->sendSuccess(200, [], 'ROLE_DELETED_SUCCESSFULLY');
    } catch (\Exception $e) {
      return $this->sendError(500, 'ERROR_DELETING_ROLE', [$e->getMessage()]);
    }
  }
}
