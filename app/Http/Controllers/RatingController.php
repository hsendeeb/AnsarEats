<?php

namespace App\Http\Controllers;

use App\Models\Rating;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RatingController extends Controller
{
    public function store(Request $request, Restaurant $restaurant)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500',
        ]);

        // Don't allow owners to rate their own restaurant
        if (Auth::id() == $restaurant->user_id) {
            return response()->json(['message' => 'You cannot rate your own restaurant.'], 403);
        }

        $rating = Rating::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'restaurant_id' => $restaurant->id,
            ],
            [
                'rating' => $request->rating,
                'comment' => $request->comment,
            ]
        );

        $avgRating = round($restaurant->averageRating(), 1);
        $totalRatings = $restaurant->ratings()->count();

        return response()->json([
            'message' => 'Rating submitted successfully!',
            'rating' => $rating,
            'average' => $avgRating,
            'total' => $totalRatings,
        ]);
    }
}
