<?php

namespace App\Http\Controllers\Api\V1\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Categories\StoreCategoryRequest;
use App\Http\Requests\Categories\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Services\CategoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class CategoryController extends Controller
{
    protected $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
        // $this->middleware('auth:sanctum');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Gate::authorize('viewAny', Category::class);

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

            return CategoryResource::collection($categories)->response()->setStatusCode(Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving categories',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCategoryRequest $request)
    {
        // Gate::authorize('create', Category::class);

        try {
            $category = $this->categoryService->createCategory($request->validated());

            // activity()
            //     ->causedBy(auth()->user())
            //     ->performedOn($category)
            //     ->withProperties(['attributes' => $request->validated()])
            //     ->log('Created category: ' . $category->name);

            return (new CategoryResource($category))
                ->response()
                ->setStatusCode(Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error creating category',
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
            $category = $this->categoryService->getCategoryById($id);
            Gate::authorize('view', $category);

            return (new CategoryResource($category))
                ->response()
                ->setStatusCode(Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Category not found or error occurred',
                'error' => $e->getMessage(),
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCategoryRequest $request, string $id)
    {
        try {
            $category = $this->categoryService->getCategoryById($id);
            // Gate::authorize('update', $category);

            $category = $this->categoryService->updateCategory($id, $request->validated());

            // activity()
            //     ->causedBy(auth()->user())
            //     ->performedOn($category)
            //     ->withProperties(['attributes' => $request->validated()])
            //     ->log('Updated category: ' . $category->name);

            return (new CategoryResource($category))
                ->response()
                ->setStatusCode(Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error updating category',
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
            $category = $this->categoryService->getCategoryById($id);
            // Gate::authorize('delete', $category);

            $this->categoryService->deleteCategory($id);

            // activity()
            //     ->causedBy(auth()->user())
            //     ->performedOn($category)
            //     ->log('Deleted category: ' . $category->name);

            return response()->json(null, Response::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error deleting category',
                'error' => $e->getMessage(),
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Retrieve activity logs for categories.
     */
    // public function activityLog(Request $request)
    // {
    //     Gate::authorize('viewAny', Activity::class);

    //     try {
    //         $perPage = $request->input('per_page', 15);
    //         $logs = Activity::where('log_name', 'category')
    //             ->orderBy('created_at', 'desc')
    //             ->paginate($perPage);

    //         return response()->json([
    //             'data' => $logs->items(),
    //             'meta' => [
    //                 'current_page' => $logs->currentPage(),
    //                 'total' => $logs->total(),
    //                 'per_page' => $logs->perPage(),
    //             ],
    //         ], Response::HTTP_OK);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'message' => 'Error retrieving activity logs',
    //             'error' => $e->getMessage(),
    //         ], Response::HTTP_INTERNAL_SERVER_ERROR);
    //     }
    // }
}