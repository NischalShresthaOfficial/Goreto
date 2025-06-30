<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisterController;
// use App\Http\Controllers\Places\PopularPlacesController;
// use App\Http\Controllers\Places\StorePlacesController;
// use App\Http\Controllers\Places\UserCategoryController;
use App\Http\Controllers\Places\CategoryAPIs\UserCategoryController;
use App\Http\Controllers\Places\FetchAPIs\PopularPlacesController;
use App\Http\Controllers\Places\LocationController;
use App\Http\Controllers\Places\SearchAPI\SearchPlacesController;
use App\Http\Controllers\Places\StoreAPIs\BhaktapurPlacesController;
use App\Http\Controllers\Places\StoreAPIs\KathmanduPlacesController;
use App\Http\Controllers\Places\StoreAPIs\LalitpurPlacesController;
use App\Http\Controllers\Reviews\FetchLocationReviewController;
use App\Http\Controllers\Reviews\LocationReviewController;
use App\Http\Controllers\UserManagement\CategoryController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Http\Controllers\Posts\CreatePostController;
use App\Http\Controllers\Posts\FetchPostController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [RegisterController::class, 'register']);
Route::post('/login', [LoginController::class, 'login']);

Route::post('/reset-password', [NewPasswordController::class, 'store']);
Route::post('/reset-password/token', [PasswordResetLinkController::class, 'store']);

// Route::post('/store-places', [StorePlacesController::class, 'fetchAndStore']);

// Route::post('/locations/popular', [LocationController::class, 'getPopularLocations']);

Route::post('/places/fetch-kathmandu', [KathmanduPlacesController::class, 'fetchKathmanduPopularPlaces']);

Route::post('/places/fetch-bhaktapur', [BhaktapurPlacesController::class, 'fetchBhaktapurPopularPlaces']);

Route::post('/places/fetch-lalitpur', [LalitpurPlacesController::class, 'fetchLalitpurPopularPlaces']);

Route::get('/places/popular', [PopularPlacesController::class, 'fetchFromDb']);

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();

    return response()->json(['message' => 'Email verified successfully']);
})->middleware(['auth:sanctum', 'signed'])->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    if ($request->user()->hasVerifiedEmail()) {
        return response()->json(['message' => 'Already verified']);
    }

    $request->user()->sendEmailVerificationNotification();

    return response()->json(['message' => 'Verification link sent!']);
})->middleware(['auth:sanctum', 'throttle:6,1'])->name('verification.send');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/logout', [LoginController::class, 'logout']);

    // Route::get('/popular-places', [PopularPlacesController::class, 'search']);

    Route::post('/categories', [CategoryController::class, 'store']);

    // Route::get('/user-category', [UserCategoryController::class, 'searchByUserCategoryOnly']);

    Route::get('/places-by-category', [UserCategoryController::class, 'fetchByUserCategories']);

    Route::post('/location-reviews', [LocationReviewController::class, 'store']);

    Route::get('/location-reviews/{locationId}', [FetchLocationReviewController::class, 'fetchByLocationId']);

    Route::get('/places/search', [SearchPlacesController::class, 'search']);

    Route::get('/places/search-history', [SearchPlacesController::class, 'fetchSearchHistory']);

     Route::prefix('posts')->group(function () {
        // Create a new post
        Route::post('/create', [CreatePostController::class, 'store']);

        // Fetch own posts
        Route::get('/my', [FetchPostController::class, 'myPosts']);

        // Fetch posts by a specific user
        Route::get('/user/{userId}', [FetchPostController::class, 'userPosts']);

        // Fetch feed (all posts)
        Route::get('/feed', [FetchPostController::class, 'feed']);
    });

});


