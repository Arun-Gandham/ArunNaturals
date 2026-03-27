<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductImage;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::orderByDesc('created_at')->paginate(15);

        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        $product = new Product();
        $categories = Category::orderBy('name')->get();

        return view('admin.products.create', compact('product', 'categories'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        if ($request->hasFile('main_image')) {
            $path = $request->file('main_image')->store('products', 'public');
            $data['main_image_path'] = 'storage/' . $path;
        }

        $product = Product::create($data);

        $product->categories()->sync($request->input('category_ids', []));

        if ($request->hasFile('gallery_images')) {
            foreach ($request->file('gallery_images') as $index => $file) {
                if (! $file || ! $file->isValid()) {
                    continue;
                }

                $path = $file->store('products/gallery', 'public');
                $product->images()->create([
                    'image_path' => 'storage/' . $path,
                    'sort_order' => $index,
                ]);
            }
        }

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Product created successfully.');
    }

    public function edit(Product $product)
    {
        $product->load('images');
        $categories = Category::orderBy('name')->get();
        $selectedCategories = $product->categories()->allRelatedIds()->all();

        return view('admin.products.edit', compact('product', 'categories', 'selectedCategories'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $this->validateData($request, $product->id);

        if ($request->hasFile('main_image')) {
            $path = $request->file('main_image')->store('products', 'public');
            $data['main_image_path'] = 'storage/' . $path;
        }

        $product->update($data);

        $product->categories()->sync($request->input('category_ids', []));

        // If new gallery images are uploaded, replace the existing gallery for this product
        if ($request->hasFile('gallery_images')) {
            $product->images()->delete();

            foreach ($request->file('gallery_images') as $index => $file) {
                if (! $file || ! $file->isValid()) {
                    continue;
                }

                $path = $file->store('products/gallery', 'public');
                $product->images()->create([
                    'image_path' => 'storage/' . $path,
                    'sort_order' => $index,
                ]);
            }
        }

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Product deleted successfully.');
    }

    public function destroyImage(ProductImage $image)
    {
        $productId = $image->product_id;
        $image->delete();

        return redirect()
            ->route('admin.products.edit', $productId)
            ->with('success', 'Gallery image removed.');
    }

    protected function validateData(Request $request, ?int $productId = null): array
    {
        $uniqueSlug = 'unique:products,slug';
        $uniqueSku = 'unique:products,sku';

        if ($productId) {
            $uniqueSlug .= ',' . $productId;
            $uniqueSku = 'unique:products,sku,' . $productId;
        }

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', $uniqueSlug],
            'sku' => ['sometimes', 'nullable', 'string', 'max:255', $uniqueSku],
            'short_title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'short_description' => ['sometimes', 'nullable', 'string'],
            'description' => ['sometimes', 'nullable', 'string'],
            'ingredients' => ['sometimes', 'nullable', 'string'],
            'usage' => ['sometimes', 'nullable', 'string'],
            'highlights' => ['sometimes', 'nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'main_image' => ['sometimes', 'nullable', 'image', 'max:2048'],
            'is_active' => ['sometimes', 'boolean'],
            'category_ids' => ['sometimes', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
            'gallery_images' => ['sometimes', 'array'],
            'gallery_images.*' => ['sometimes', 'image', 'max:2048'],
        ]);
    }
}
