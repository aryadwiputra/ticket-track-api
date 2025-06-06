<?php

namespace App\Http\Controllers\Api\V1\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\Users\StoreUserRequest;
use App\Http\Requests\Users\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\Activitylog\Models\Activity;
use Symfony\Component\HttpFoundation\Response;


class UserController extends Controller implements HasMiddleware
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public static function middleware(): array
    {
        return [
            new Middleware('permission:users.view', only: ['index', 'show']),
            new Middleware('permission:users.create', only: ['store']),
            new Middleware('permission:users.update', only: ['update']),
            new Middleware('permission:users.delete', only: ['destroy']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 15);
            $sortBy = $request->input('sort_by', 'created_at');
            $sortOrder = $request->input('sort_order', 'desc');

            $users = $this->userService->getUsers([
                'per_page' => $perPage,
                'sort_by' => $sortBy,
                'sort_order' => $sortOrder,
                'search' => $request->input('search'),
            ]);


            return UserResource::collection($users)->response()->setStatusCode(Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving users',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        try {
            $user = $this->userService->createUser($request->validated());

            // Manual logging for custom details
            activity()
                ->causedBy(auth()->user())
                ->performedOn($user)
                ->withProperties(['attributes' => $request->validated()])
                ->log('Created user with email: ' . $user->email);

            return (new UserResource($user))
                ->response()
                ->setStatusCode(Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error creating user',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $user = $this->userService->getUserById($id);
            return (new UserResource($user))
                ->response()
                ->setStatusCode(Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'User not found or error occurred',
                'error' => $e->getMessage(),
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, string $id)
    {
        try {
            $user = $this->userService->getUserById($id);
            $user = $this->userService->updateUser($id, $request->validated());

            // Manual logging for custom details
            activity()
                ->causedBy(auth()->user())
                ->performedOn($user)
                ->withProperties(['attributes' => $request->validated()])
                ->log('Updated user with email: ' . $user->email);

            return (new UserResource($user))
                ->response()
                ->setStatusCode(Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error updating user',
                'error' => $e->getMessage(),
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $this->userService->deleteUser($id);
            return response()->json(null, Response::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error deleting user',
                'error' => $e->getMessage(),
            ], Response::HTTP_NOT_FOUND);
        }
    }
    public function activityLog(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 15);
            $logs = Activity::where('log_name', 'user')
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return response()->json([
                'data' => $logs->items(),
                'meta' => [
                    'current_page' => $logs->currentPage(),
                    'total' => $logs->total(),
                    'per_page' => $logs->perPage(),
                ],
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving activity logs',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}