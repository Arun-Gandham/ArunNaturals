<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Show a public product detail page from the database.
     */
    public function show(string $slug)
    {
        $product = Product::with('images')
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $imageUrl = $product->main_image_path
            ? asset($product->main_image_path)
            : null;

        $ingredients = $product->ingredients
            ? preg_split('/\r\n|\r|\n/', trim($product->ingredients))
            : [];

        $usage = $product->usage
            ? preg_split('/\r\n|\r|\n/', trim($product->usage))
            : [];

        $highlights = $product->highlights
            ? preg_split('/\r\n|\r|\n/', trim($product->highlights))
            : [];

        $galleryImages = $product->images
            ? $product->images->pluck('image_path')->map(fn ($p) => asset($p))->all()
            : [];

        $viewProduct = [
            'name'              => $product->name,
            'tagline'           => $product->short_title,
            'short_description' => $product->short_description,
            'image'             => $imageUrl,
            'ingredients'       => $ingredients,
            'usage'             => $usage,
            'highlights'        => $highlights,
        ];

        $whatsappPhone = config('services.whatsapp.phone');
        $whatsappText = sprintf(
            'Hi Arun Naturals, I am interested in %s. Please share more details and buying options.',
            $product->name
        );

        return view('store.product-show', [
            'product'       => $viewProduct,
            'galleryImages' => $galleryImages,
            'whatsappPhone' => $whatsappPhone,
            'whatsappText'  => $whatsappText,
        ]);
    }
}
