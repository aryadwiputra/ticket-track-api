<?php

namespace App\Http\Controllers\Api\V1\Users;

use App\Http\Controllers\Api\V1\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;


use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class PermissionController extends BaseController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:permissions-access', only: ['index', 'show']),
            new Middleware('permission:permissions-create', only: ['store']),
            new Middleware('permission:permissions-update', only: ['update']),
            new Middleware('permission:permissions-delete', only: ['destroy']),
        ];
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $permissions = Permission::all();

        return $this->sendSuccess(200, $permissions->toArray(), 'PERMISSIONS_RETRIEVED_SUCCESSFULLY');
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "name" => "required|unique:permissions",
        ]);

        if ($validator->fails()) {
            return $this->sendError(422, 'error', $validator->errors()->all());
        }

        $permission = Permission::create($request->all());

        return $this->sendSuccess(200, $permission->toArray(), 'PERMISSION_CREATED_SUCCESSFULLY');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $permission = Permission::find($id);

        if (!$permission) {
            return $this->sendError(404, 'PERMISSION_NOT_FOUND');
        }

        return $this->sendSuccess(200, $permission->toArray(), 'PERMISSION_RETRIEVED_SUCCESSFULLY');
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

        $permission = Permission::find($id);

        if (!$permission) {
            return $this->sendError(404, 'PERMISSION_NOT_FOUND');
        }

        $permission->update([
            "name" => $request->name,
        ]);

        return $this->sendSuccess(200, $permission->toArray(), 'PERMISSION_UPDATED_SUCCESSFULLY');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $permission = Permission::find($id);

        if (!$permission) {
            return $this->sendError(404, 'NOT_FOUND');
        }

        $permission->delete();

        return $this->sendSuccess(200, [], 'PERMISSION_DELETED_SUCCESSFULLY');
    }
}
