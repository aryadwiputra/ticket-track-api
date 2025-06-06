<?php

namespace App\Http\Controllers\Api\V1\Users;

use App\Http\Controllers\Api\V1\BaseController;
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


class UserController extends BaseController implements HasMiddleware
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public static function middleware(): array
    {
        return [
            new Middleware('custom_spatie_forbidden:users-access', only: ['index', 'show']),
            new Middleware('custom_spatie_forbidden:users-create', only: ['store']),
            new Middleware('custom_spatie_forbidden:users-update', only: ['update']),
            new Middleware('custom_spatie_forbidden:users-delete', only: ['destroy']),
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

            return $this->sendSuccess(
                Response::HTTP_OK,
                UserResource::collection($users),
                'USERS_RETRIEVED_SUCCESSFULLY'
            );
        } catch (\Exception $e) {
            return $this->sendError(
                Response::HTTP_INTERNAL_SERVER_ERROR,
                'Error retrieving users',
                [$e->getMessage()]
            );
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

            return $this->sendSuccess(
                Response::HTTP_CREATED,
                UserResource::make($user),
                'USER_CREATED_SUCCESSFULLY'
            );
        } catch (\Exception $e) {
            return $this->sendError(
                Response::HTTP_INTERNAL_SERVER_ERROR,
                'Error creating user',
                [$e->getMessage()]
            );
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $user = $this->userService->getUserById($id);
            return $this->sendSuccess(
                Response::HTTP_OK,
                new UserResource($user),
                'USER_RETRIEVED_SUCCESSFULLY'
            );
        } catch (\Exception $e) {
            return $this->sendError(
                Response::HTTP_NOT_FOUND,
                'ERROR_RETRIEVING_USER',
                [$e->getMessage()]
            );
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

            return $this->sendSuccess(
                Response::HTTP_OK,
                new UserResource($user),
                'USER_UPDATED_SUCCESSFULLY'
            );
        } catch (\Exception $e) {
            return $this->sendError(
                Response::HTTP_NOT_FOUND,
                'ERROR_UPDATING_USER',
                [$e->getMessage()]
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $this->userService->deleteUser($id);
            // Manual logging for custom details
            activity()
                ->causedBy(auth()->user())
                ->withProperties(['user_id' => $id])
                ->log('Deleted user with ID: ' . $id);
            return $this->sendSuccess(
                Response::HTTP_OK,
                null,
                'USER_DELETED_SUCCESSFULLY'
            );
        } catch (\Exception $e) {
            return $this->sendError(
                Response::HTTP_NOT_FOUND,
                'ERROR_DELETING_USER',
                [$e->getMessage()]
            );
        }
    }

    public function activityLog(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 15);
            $logs = Activity::where('log_name', 'user')
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return $this->sendSuccess(
                Response::HTTP_OK,
                $logs,
                'USER_ACTIVITY_LOGS_RETRIEVED_SUCCESSFULLY'
            );
        } catch (\Exception $e) {
            return $this->sendError(
                Response::HTTP_INTERNAL_SERVER_ERROR,
                'Error retrieving activity logs',
                [$e->getMessage()]
            );
        }
    }
}