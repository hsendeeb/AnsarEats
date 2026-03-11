<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\RestaurantController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\Owner\DashboardController;
use App\Http\Controllers\Owner\MenuItemController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RatingController;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/restaurants', [RestaurantController::class, 'index'])->name('restaurants.index');
Route::get('/restaurant/{restaurant}', [RestaurantController::class, 'show'])->name('restaurant.show');
Route::get('/search/suggestions', [SearchController::class, 'suggestions'])->name('search.suggestions');

// Basic demo auth routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
Route::get('/register', [LoginController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [LoginController::class, 'register']);

// Cart routes (public — guests can use the cart)
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');

// Checkout (auth required)
Route::middleware('auth')->group(function () {
    Route::get('/checkout', [CartController::class, 'checkout'])->name('checkout');
    Route::post('/checkout', [CartController::class, 'placeOrder'])->name('checkout.place');
    Route::get('/order/{order}/confirmation', [CartController::class, 'confirmation'])->name('order.confirmation');

    // Rating
    Route::post('/restaurant/{restaurant}/rate', [RatingController::class, 'store'])->name('restaurant.rate');

    // Owner routes
    Route::prefix('owner')->name('owner.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/chart-data', [DashboardController::class, 'chartData'])->name('dashboard.chart-data');
        Route::post('/restaurant', [DashboardController::class, 'storeOrUpdate'])->name('restaurant.store');
        Route::post('/category', [DashboardController::class, 'storeCategory'])->name('category.store');
        Route::put('/category/{category}', [DashboardController::class, 'updateCategory'])->name('category.update');
        Route::delete('/category/{category}', [DashboardController::class, 'destroyCategory'])->name('category.destroy');
        Route::post('/menu-item', [MenuItemController::class, 'store'])->name('menu-item.store');
        Route::put('/menu-item/{menuItem}', [MenuItemController::class, 'update'])->name('menu-item.update');
        Route::post('/menu-item/{menuItem}/toggle', [MenuItemController::class, 'toggleAvailability'])->name('menu-item.toggle');
        Route::delete('/menu-item/{menuItem}', [MenuItemController::class, 'destroy'])->name('menu-item.destroy');
        Route::post('/order/{order}/accept', [DashboardController::class, 'acceptOrder'])->name('order.accept');
    });

    // Profile & Orders
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/orders', [ProfileController::class, 'orders'])->name('profile.orders');
    Route::post('/orders/clear', [ProfileController::class, 'clearHistory'])->name('profile.clear');
});
