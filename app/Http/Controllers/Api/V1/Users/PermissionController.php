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
            new Middleware('auth:api'),
        ];
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $permissions = Permission::all();

        return $this->sendSuccess(200, $permissions);
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
            return $this->sendError(422, 'error', $validator->errors());
        }

        $permission = Permission::create($request->all());

        return $this->sendSuccess(200, 'success', $permission);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $permission = Permission::find($id);

        if (!$permission) {
            return $this->sendError(404, 'not_found', 'Permission not found');
        }

        return $this->sendSuccess(200, 'success', $permission);
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

        $permission = Permission::find($id);

        if (!$permission) {
            return $this->sendError(404, 'not_found', 'Permission not found');
        }

        $permission->update([
            "name" => $request->name,
        ]);

        return $this->sendSuccess(200, 'success', $permission);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $permission = Permission::find($id);

        if (!$permission) {
            return $this->sendError(404, 'not_found', 'Permission not found');
        }

        $permission->delete();

        return $this->sendSuccess(200, 'success', []);
    }
}
