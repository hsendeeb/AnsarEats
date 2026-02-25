<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApiLoginRequest;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(ApiLoginRequest $request) {
    $users=User::all();   
    return response()->json([
            'user'=>$users
        ],200);
    }
}
