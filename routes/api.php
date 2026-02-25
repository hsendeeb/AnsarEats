<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Models\Ticket;

Route::get('/login',[AuthController::class,'login']);
Route::get('/tickets',function () {
    return Ticket::all();
});