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

use App\Http\Controllers\BrowseController;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/restaurants', [RestaurantController::class, 'index'])->name('restaurants.index');
Route::get('/restaurant/{restaurant}', [RestaurantController::class, 'show'])->name('restaurant.show');
Route::get('/search/suggestions', [SearchController::class, 'suggestions'])->name('search.suggestions');
Route::get('/browse', [BrowseController::class, 'index'])->name('browse.index');

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
Route::post('/cart/promo', [CartController::class, 'applyPromo'])->name('cart.promo');

// Checkout (auth required)
Route::middleware('auth')->group(function () {
    Route::get('/checkout', [CartController::class, 'checkout'])->name('checkout');
    Route::post('/checkout', [CartController::class, 'placeOrder'])->name('checkout.place');
    Route::get('/order/{order}/confirmation', [CartController::class, 'confirmation'])->name('order.confirmation');
    Route::get('/order/{order}/status', [CartController::class, 'pollStatus'])->name('order.status');
    Route::get('/orders/batch-status', [CartController::class, 'batchStatus'])->name('orders.batch-status');

    // Rating
    Route::post('/restaurant/{restaurant}/rate', [RatingController::class, 'store'])->name('restaurant.rate');

    // Owner routes
    Route::prefix('owner')->name('owner.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/orders', [DashboardController::class, 'orders'])->name('orders');
        Route::get('/dashboard/chart-data', [DashboardController::class, 'chartData'])->name('dashboard.chart-data');
        Route::get('/dashboard/poll-new-orders', [DashboardController::class, 'pollNewOrders'])->name('dashboard.poll-new-orders');
        Route::post('/restaurant', [DashboardController::class, 'storeOrUpdate'])->name('restaurant.store');
        Route::post('/restaurant/toggle-status', [DashboardController::class, 'toggleRestaurantStatus'])->name('restaurant.toggle-status');
        
        Route::post('/category', [DashboardController::class, 'storeCategory'])->name('category.store');
        Route::put('/category/{category}', [DashboardController::class, 'updateCategory'])->name('category.update');
        Route::post('/category/{category}/toggle-visibility', [DashboardController::class, 'toggleCategoryVisibility'])->name('category.toggle-visibility');
        Route::delete('/category/{category}', [DashboardController::class, 'destroyCategory'])->name('category.destroy');
        
        Route::post('/menu-item', [MenuItemController::class, 'store'])->name('menu-item.store');
        Route::put('/menu-item/{menuItem}', [MenuItemController::class, 'update'])->name('menu-item.update');
        Route::post('/menu-item/{item}/toggle-availability', [DashboardController::class, 'toggleItemAvailability'])->name('menu-item.toggle');
        Route::post('/menu-item/{item}/toggle-featured', [DashboardController::class, 'toggleItemFeatured'])->name('menu-item.toggle-featured');
        Route::delete('/menu-item/{menuItem}', [MenuItemController::class, 'destroy'])->name('menu-item.destroy');
        
        Route::post('/order/{order}/accept', [DashboardController::class, 'acceptOrder'])->name('order.accept');
        Route::post('/order/{order}/prepare', [DashboardController::class, 'prepareOrder'])->name('order.prepare');
        Route::post('/order/{order}/out-for-delivery', [DashboardController::class, 'outForDeliveryOrder'])->name('order.out-for-delivery');
        Route::post('/order/{order}/reject', [DashboardController::class, 'rejectOrder'])->name('order.reject');
        Route::post('/order/{order}/deliver', [DashboardController::class, 'deliverOrder'])->name('order.deliver');
        Route::get('/order/{order}/print', [DashboardController::class, 'printOrder'])->name('order.print');

        Route::post('/promotion', [DashboardController::class, 'storePromotion'])->name('promotion.store');
        Route::delete('/promotion/{promotion}', [DashboardController::class, 'destroyPromotion'])->name('promotion.destroy');
    });

    // Profile & Orders
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/orders', [ProfileController::class, 'orders'])->name('profile.orders');
    Route::post('/orders/clear', [ProfileController::class, 'clearHistory'])->name('profile.clear');
});
