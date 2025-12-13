<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class CategoryController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $this->authorize('viewAny', Category::class);
        $categories = Category::withCount('projects')->latest()->get();
        return view('categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Category::class);

        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:categories,name',
        ]);

        $category = Category::create($validated);
        $category->loadCount('projects');

        return response()->json($category, 201);
    }

    public function update(Request $request, Category $category)
    {
        $this->authorize('update', $category);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50', Rule::unique('categories')->ignore($category->id)],
        ]);

        $category->update($validated);
        $category->loadCount('projects');

        return response()->json($category);
    }

    public function destroy(Category $category)
    {
        $this->authorize('delete', $category);

        if ($category->projects()->exists()) {
            return response()->json(['message' => 'Cannot delete: Category is attached to projects.'], 422);
        }

        $category->delete();

        return response()->json(['message' => 'Category deleted successfully.']);
    }
}