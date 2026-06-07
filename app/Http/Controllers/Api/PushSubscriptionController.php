<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PushSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PushSubscriptionController extends Controller
{
    public function subscribe(Request $request)
    {
        $request->validate([
            'endpoint' => 'required|string|url',
            'public_key' => 'nullable|string',
            'auth_token' => 'nullable|string',
        ]);

        $user = Auth::guard('web')->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        PushSubscription::updateOrCreate(
            ['endpoint' => $request->endpoint],
            [
                'user_id' => $user->id,
                'public_key' => $request->public_key,
                'auth_token' => $request->auth_token,
            ]
        );

        return response()->json(['success' => true]);
    }

    public function unsubscribe(Request $request)
    {
        $request->validate([
            'endpoint' => 'required|string|url',
        ]);

        PushSubscription::where('endpoint', $request->endpoint)->delete();

        return response()->json(['success' => true]);
    }
}
