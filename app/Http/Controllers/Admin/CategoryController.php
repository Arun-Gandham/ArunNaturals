<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::orderBy('name')->paginate(15);

        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        $category = new Category();

        return view('admin.categories.create', compact('category'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        if (! $data['slug']) {
            $data['slug'] = Str::slug($data['name']);
        }

        $data['is_active'] = $request->boolean('is_active');

        Category::create($data);

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Category created successfully.');
    }

    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $data = $this->validateData($request, $category->id);

        if (! $data['slug']) {
            $data['slug'] = Str::slug($data['name']);
        }

        $data['is_active'] = $request->boolean('is_active');

        $category->update($data);

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category)
    {
        $category->delete();

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Category deleted successfully.');
    }

    protected function validateData(Request $request, ?int $categoryId = null): array
    {
        $uniqueSlug = 'unique:categories,slug';
        if ($categoryId) {
            $uniqueSlug .= ',' . $categoryId;
        }

        return $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'slug'        => ['nullable', 'string', 'max:255', $uniqueSlug],
            'description' => ['nullable', 'string'],
            'is_active'   => ['sometimes', 'boolean'],
        ]);
    }
}

