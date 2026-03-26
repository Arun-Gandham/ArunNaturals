<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Show a public product detail page.
     */
    public function show(string $slug)
    {
        // Simple in-memory catalogue; extend as needed.
        $products = [
            'herbal-face-oil' => [
                'name' => 'Herbal Face Oil',
                'tagline' => 'Calming nourishment for naturally glowing skin.',
                'short_description' => 'A lightweight blend of cold-pressed oils and herbal extracts crafted to support daily skin repair, radiance and softness.',
                'image' => '/images/products/herbal-face-oil.jpg',
                'ingredients' => [
                    'Cold-pressed sesame and almond oils',
                    'Manjistha, licorice and turmeric extracts',
                    'Vitamin E and plant-based antioxidants',
                    'No mineral oil, parabens or artificial fragrance',
                ],
                'usage' => [
                    'Use on clean, dry skin (morning and/or night).',
                    'Warm 2–3 drops between your palms and press gently onto face and neck.',
                    'Massage in upward, circular motions until absorbed.',
                    'Patch test recommended for sensitive skin.',
                ],
                'highlights' => [
                    'Dermatologically inspired, rooted in traditional formulations.',
                    'Made in small batches for maximum freshness.',
                    'Suitable for all skin types; especially normal to dry.',
                ],
            ],
            'herbal-hair-oil' => [
                'name' => 'Herbal Hair Oil',
                'tagline' => 'Root-deep care for stronger, calmer hair.',
                'short_description' => 'An infusion of traditional herbs in lightweight oils designed to support scalp comfort, hair strength and natural shine.',
                'image' => '/images/products/herbal-hair-oil.jpg',
                'ingredients' => [
                    'Coconut and sesame oil base',
                    'Bhringraj, amla, hibiscus and fenugreek',
                    'Natural vitamin E and plant actives',
                    'No added colour, mineral oil or silicones',
                ],
                'usage' => [
                    'Gently warm the oil if desired.',
                    'Apply to scalp and lengths; massage with fingertips for 5–10 minutes.',
                    'Leave on for at least 30 minutes or overnight.',
                    'Cleanse with a mild shampoo; use 2–3 times a week.',
                ],
                'highlights' => [
                    'Helps support reduced hair breakage with regular use.',
                    'Comforting head massage ritual for stress relief.',
                    'Suitable for most hair types; adjust quantity for fine hair.',
                ],
            ],
        ];

        if (! array_key_exists($slug, $products)) {
            abort(404);
        }

        $product = $products[$slug];

        $whatsappPhone = config('services.whatsapp.phone');
        $whatsappText = sprintf(
            'Hi Arun Naturals, I am interested in %s. Please share more details and buying options.',
            $product['name']
        );

        return view('store.product-show', [
            'slug' => $slug,
            'product' => $product,
            'whatsappPhone' => $whatsappPhone,
            'whatsappText' => $whatsappText,
        ]);
    }
}

