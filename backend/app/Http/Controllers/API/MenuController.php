<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\BusinessHour;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    /**
     * Get Complete Digital Menu with Categories and Products
     */
    public function index(Request $request)
    {
        $locale = $request->query('locale', 'am');
        $categoryId = $request->query('category_id');
        $search = $request->query('search');
        $fastingOnly = $request->boolean('fasting_only');

        $query = Category::with([
            'products' => function ($q) use ($search, $fastingOnly) {
                $q->where('is_available', true)
                  ->with(['translations', 'variants', 'addons']);

                if ($fastingOnly) {
                    $q->where('is_fasting', true);
                }

                if ($search) {
                    $q->whereHas('translations', function ($t) use ($search) {
                        $t->where('name', 'like', "%{$search}%")
                          ->orWhere('description', 'like', "%{$search}%");
                    });
                }
            }
        ])->where('is_active', true)->orderBy('display_order');

        if ($categoryId) {
            $query->where('id', $categoryId);
        }

        $categories = $query->get()->map(function ($cat) use ($locale) {
            return [
                'id' => $cat->id,
                'slug' => $cat->slug,
                'name' => $locale === 'am' ? $cat->name_am : $cat->name_en,
                'description' => $locale === 'am' ? $cat->description_am : $cat->description_en,
                'icon' => $cat->icon,
                'image_url' => $cat->image_url,
                'products' => $cat->products->map(function ($prod) use ($locale) {
                    return [
                        'id' => $prod->id,
                        'name' => $prod->getLocalizedName($locale),
                        'description' => $prod->getLocalizedDescription($locale),
                        'price' => (float) $prod->price,
                        'currency' => 'ETB',
                        'preparation_time_minutes' => $prod->preparation_time_minutes,
                        'image_url' => $prod->image_url,
                        'is_available' => $prod->is_available,
                        'is_fasting' => $prod->is_fasting,
                        'variants' => $prod->variants->map(fn($v) => [
                            'id' => $v->id,
                            'name' => $locale === 'am' ? $v->variant_name_am : $v->variant_name_en,
                            'price_modifier' => (float) $v->price_modifier,
                            'is_default' => $v->is_default,
                        ]),
                        'addons' => $prod->addons->where('is_active', true)->values()->map(fn($a) => [
                            'id' => $a->id,
                            'name' => $locale === 'am' ? $a->addon_name_am : $a->addon_name_en,
                            'extra_price' => (float) $a->extra_price,
                        ]),
                    ];
                }),
            ];
        });

        // Current opening status
        $dayOfWeek = now()->dayOfWeek;
        $hours = BusinessHour::where('day_of_week', $dayOfWeek)->first();
        $isOpen = true;
        if ($hours) {
            $currentTime = now()->toTimeString();
            $isOpen = !$hours->is_closed && ($currentTime >= $hours->open_time && $currentTime <= $hours->close_time);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'categories' => $categories,
                'cafe_status' => [
                    'is_open' => $isOpen,
                    'open_time' => $hours?->open_time ?? '06:30',
                    'close_time' => $hours?->close_time ?? '22:30',
                    'currency' => 'ETB',
                    'branch' => 'Fkr Cafe, Kombolcha',
                ],
            ],
        ]);
    }

    /**
     * Manager: Toggle Product Availability
     */
    public function toggleAvailability(Request $request, int $id)
    {
        $product = Product::findOrFail($id);
        $product->is_available = !$product->is_available;
        $product->save();

        return response()->json([
            'success' => true,
            'message' => "Product availability updated to " . ($product->is_available ? 'Available' : 'Unavailable'),
            'data' => $product,
        ]);
    }
}
