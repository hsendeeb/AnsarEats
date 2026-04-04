<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use App\Models\MenuCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MenuItemController extends Controller
{
    private function validateMenuItem(Request $request): array
    {
        return $request->validate([
            'menu_category_id' => 'sometimes|required|exists:menu_categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp',
            'has_variants' => 'sometimes|boolean',
            'variant_type' => 'required_if:has_variants,1|nullable|string|max:255',
            'variant_names' => 'required_if:has_variants,1|nullable|array',
            'variant_names.*' => 'nullable|string|max:255',
            'variant_prices' => 'required_if:has_variants,1|nullable|array',
            'variant_prices.*' => 'nullable|numeric|min:0',
            'is_on_sale' => 'sometimes|boolean',
            'discount_percentage' => 'nullable|required_if:is_on_sale,1|numeric|min:0.01|max:100',
        ]);
    }

    private function buildVariantsPayload(Request $request): ?array
    {
        if (! $request->boolean('has_variants')) {
            return null;
        }

        $names = $request->input('variant_names', []);
        $prices = $request->input('variant_prices', []);
        $options = [];

        foreach ($names as $index => $name) {
            $name = trim((string) $name);
            $price = $prices[$index] ?? null;

            if ($name === '' || $price === null || $price === '') {
                continue;
            }

            $options[] = [
                'label' => $name,
                'price' => (float) $price,
            ];
        }

        if (empty($options)) {
            return null;
        }

        return [
            'type' => $request->input('variant_type'),
            'options' => $options,
        ];
    }

    private function calculateDiscountedPrice(float $price, float $discountPercentage): float
    {
        return round(max($price * (1 - ($discountPercentage / 100)), 0), 2);
    }

    private function applySaleFields(array &$data, Request $request): void
    {
        $data['is_on_sale'] = $request->boolean('is_on_sale');

        if (! $data['is_on_sale']) {
            $data['sale_price'] = null;
            $data['discount_percentage'] = null;

            return;
        }

        $discountPercentage = round((float) $request->input('discount_percentage'), 2);

        $data['discount_percentage'] = $discountPercentage;
        $data['sale_price'] = $this->calculateDiscountedPrice((float) $data['price'], $discountPercentage);
    }

    public function store(Request $request)
    {
        $this->validateMenuItem($request);

        $category = MenuCategory::findOrFail($request->menu_category_id);

        // Ensure the category belongs to the current user's restaurant
        if ($category->restaurant->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $data = $request->only('name', 'description', 'price');
        $data['variants'] = $this->buildVariantsPayload($request);
        $this->applySaleFields($data, $request);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('menu-items', 'public');
        }

        $category->menuItems()->create($data);

        return back()->with('success', 'Menu item added successfully!');
    }

    public function toggleAvailability(MenuItem $menuItem)
    {
        if ($menuItem->menuCategory->restaurant->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $menuItem->update(['is_available' => !$menuItem->is_available]);

        return back()->with('success', 'Availability updated!');
    }

    public function update(Request $request, MenuItem $menuItem)
    {
        if ($menuItem->menuCategory->restaurant->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $this->validateMenuItem($request);

        $data = $request->only('name', 'description', 'price');
        $data['variants'] = $this->buildVariantsPayload($request);
        $this->applySaleFields($data, $request);

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($menuItem->image) {
                Storage::disk('public')->delete($menuItem->image);
            }
            $data['image'] = $request->file('image')->store('menu-items', 'public');
        }

        $menuItem->update($data);

        return back()->with('success', 'Menu item updated successfully!');
    }

    public function destroy(MenuItem $menuItem)
    {
        if ($menuItem->menuCategory->restaurant->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $menuItem->delete();

        return back()->with('success', 'Item deleted!');
    }
}
