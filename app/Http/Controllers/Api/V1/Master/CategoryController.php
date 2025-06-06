<?php

namespace App\Http\Controllers\Api\V1\Master;

use App\Http\Controllers\Api\V1\BaseController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Categories\StoreCategoryRequest;
use App\Http\Requests\Categories\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Services\CategoryService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Spatie\Activitylog\Models\Activity;
use Symfony\Component\HttpFoundation\Response;

class CategoryController extends BaseController implements HasMiddleware
{
    protected $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    public static function middleware(): array
    {
        return [
            new Middleware('custom_spatie_forbidden:categories-access', only: ['index', 'show']),
            new Middleware('custom_spatie_forbidden:categories-create', only: ['store']),
            new Middleware('custom_spatie_forbidden:categories-update', only: ['update']),
            new Middleware('custom_spatie_forbidden:categories-delete', only: ['destroy']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 15);
            $sortBy = $request->input('sort_by', 'sort_order');
            $sortOrder = $request->input('sort_order', 'asc');
            $search = $request->input('search');

            $categories = $this->categoryService->getCategories([
                'per_page' => $perPage,
                'sort_by' => $sortBy,
                'sort_order' => $sortOrder,
                'search' => $search,
            ]);

            return $this->sendSuccess(200, CategoryResource::collection($categories), 'CATEGORIES_RETRIEVED_SUCCESSFULLY');
        } catch (\Exception $e) {
            return $this->sendError(500, 'Error retrieving categories', [$e->getMessage()]);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCategoryRequest $request)
    {
        try {
            $category = $this->categoryService->createCategory($request->validated());

            activity()
                ->causedBy(auth()->user())
                ->performedOn($category)
                ->withProperties(['attributes' => $request->validated()])
                ->log('Created category: ' . $category->name);

            return $this->sendSuccess(
                201,
                new CategoryResource($category),
                'CATEGORY_CREATED_SUCCESSFULLY'
            );
        } catch (\Exception $e) {
            return $this->sendError(500, 'Error creating category', [$e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $category = $this->categoryService->getCategoryById($id);

            return $this->sendSuccess(200, new CategoryResource($category), 'CATEGORY_RETRIEVED_SUCCESSFULLY');
        } catch (\Exception $e) {
            return $this->sendError(404, 'ERROR_RETRIEVING_CATEGORY', [$e->getMessage()]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCategoryRequest $request, string $id)
    {
        try {
            $category = $this->categoryService->getCategoryById($id);

            $category = $this->categoryService->updateCategory($id, $request->validated());

            activity()
                ->causedBy(auth()->user())
                ->performedOn($category)
                ->withProperties(['attributes' => $request->validated()])
                ->log('Updated category: ' . $category->name);

            return $this->sendSuccess(
                200,
                new CategoryResource($category),
                'CATEGORY_UPDATED_SUCCESSFULLY'
            );
        } catch (\Exception $e) {
            return $this->sendError(404, 'ERROR_UPDATING_CATEGORY', [$e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $category = $this->categoryService->getCategoryById($id);

            $this->categoryService->deleteCategory($id);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($category)
                ->log('Deleted category: ' . $category->name);

            return $this->sendSuccess(204, null, 'CATEGORY_DELETED_SUCCESSFULLY');
        } catch (\Exception $e) {
            return $this->sendError(404, 'ERROR_DELETING_CATEGORY', [$e->getMessage()]);
        }
    }

    /**
     * Retrieve activity logs for categories.
     */
    public function activityLog(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 15);
            $logs = Activity::where('log_name', 'category')
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return $this->sendSuccess(200, $logs, 'ACTIVITY_LOGS_RETRIEVED_SUCCESSFULLY');
        } catch (\Exception $e) {
            return $this->sendError(500, 'Error retrieving activity logs', [$e->getMessage()]);
        }
    }
}