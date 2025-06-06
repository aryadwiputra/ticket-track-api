<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class CategoryService
{
  public function getCategories(array $params): LengthAwarePaginator
  {
    $query = Category::query();

    if (!empty($params['search'])) {
      $query->where('name', 'like', '%' . $params['search'] . '%')
        ->orWhere('description', 'like', '%' . $params['search'] . '%');
    }

    return $query->orderBy($params['sort_by'], $params['sort_order'])
      ->paginate($params['per_page']);
  }

  public function getCategoryById(string $id): Category
  {
    return Category::findOrFail($id);
  }

  public function createCategory(array $data): Category
  {
    $data['slug'] = Str::slug($data['name']);
    $data['is_active'] = isset($data['is_active']) ? (bool) $data['is_active'] : true;
    $data['sort_order'] = $data['sort_order'] ?? 0;

    return Category::create($data);
  }

  public function updateCategory(string $id, array $data): Category
  {
    $category = $this->getCategoryById($id);

    if (isset($data['name'])) {
      $data['slug'] = Str::slug($data['name']);
    }
    $data['is_active'] = isset($data['is_active']) ? (bool) $data['is_active'] : $category->is_active;
    $data['sort_order'] = $data['sort_order'] ?? $category->sort_order;

    $category->update($data);
    return $category;
  }

  public function deleteCategory(string $id): void
  {
    $category = $this->getCategoryById($id);

    // Prevent deletion if category has children
    if ($category->children()->exists()) {
      throw new \Exception('Cannot delete category with subcategories');
    }

    $category->delete();
  }
}