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
    public function store(Request $request)
    {
        $request->validate([
            'menu_category_id' => 'required|exists:menu_categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp',
        ]);

        $category = MenuCategory::findOrFail($request->menu_category_id);
        
        // Ensure the category belongs to the current user's restaurant
        if ($category->restaurant->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $data = $request->only('name', 'description', 'price');

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

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp',
        ]);

        $data = $request->only('name', 'description', 'price');

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
