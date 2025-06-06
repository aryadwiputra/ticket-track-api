<?php

namespace App\Http\Controllers\Api\V1\Users;

use App\Http\Controllers\Api\V1\BaseController;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Validator;
use Reflector;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;


class RoleController extends BaseController implements HasMiddleware
{
  public static function middleware(): array
  {
    return [
      new Middleware('auth:api'),
    ];
  }

  public function index()
  {
    $roles = Role::all();

    return $this->sendSuccess(200, $roles);
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
      return $this->sendError(422, 'error', $validator->errors());
    }

    $role = Role::create($request->all());

    return $this->sendSuccess(200, 'success', $role);
  }

  public function show(string $id)
  {
    $role = Role::find($id);

    if (!$role) {
      return $this->sendError(404, 'not_found', 'Role not found');
    }

    return $this->sendSuccess(200, 'success', $role);
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
      return $this->sendError(422, 'error', $validator->errors());
    }

    $role = Role::find($id);

    if (!$role) {
      return $this->sendError(404, 'not_found', 'Role not found');
    }

    $role->update([
      "name" => $request->name,
    ]);

    return $this->sendSuccess(200, 'success', $role);
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(string $id)
  {
    $role = Role::find($id);

    if (!$role) {
      return $this->sendError(404, 'not_found', 'Role not found');
    }

    $role->delete();

    return $this->sendSuccess(200, 'success', []);
  }
}
