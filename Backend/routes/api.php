<?php

use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Chats\ChatController;
use App\Http\Controllers\Chats\ChatMessageController;
use App\Http\Controllers\Chats\NearbyActiveUsersController;
use App\Http\Controllers\Chats\UserActivityStatusController;
use App\Http\Controllers\Chats\UserLocationController;
use App\Http\Controllers\Places\CategoryAPIs\CategoryPlacesController;
use App\Http\Controllers\Places\CategoryAPIs\UserCategoryController;
use App\Http\Controllers\Places\FetchAPIs\PopularPlacesController;
use App\Http\Controllers\Places\LocationImages\LocationImageController;
use App\Http\Controllers\Places\SearchAPI\SearchPlacesController;
use App\Http\Controllers\Places\StoreAPIs\BhaktapurPlacesController;
use App\Http\Controllers\Places\StoreAPIs\KathmanduPlacesController;
use App\Http\Controllers\Places\StoreAPIs\KavrepalanchowkPlacesController;
use App\Http\Controllers\Places\StoreAPIs\LalitpurPlacesController;
use App\Http\Controllers\Places\StoreAPIs\NuwakotPlacesController;
use App\Http\Controllers\Reviews\FetchLocationReviewController;
use App\Http\Controllers\Reviews\LocationReviewController;
use App\Http\Controllers\UserManagement\CategoryController;
use App\Http\Controllers\UserManagement\FavouriteLocationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [RegisterController::class, 'register']);
Route::post('/verify-email', [EmailVerificationController::class, 'verifyEmail']);
Route::post('/login', [LoginController::class, 'login']);

Route::post('/reset-password', [NewPasswordController::class, 'store']);
Route::post('/reset-password/token', [PasswordResetLinkController::class, 'store']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::middleware(['auth:sanctum', 'role:super_admin'])->group(function () {
        Route::post('/places/fetch-kathmandu', [KathmanduPlacesController::class, 'fetchKathmanduPopularPlaces']);

        Route::post('/places/fetch-bhaktapur', [BhaktapurPlacesController::class, 'fetchBhaktapurPopularPlaces']);

        Route::post('/places/fetch-lalitpur', [LalitpurPlacesController::class, 'fetchLalitpurPopularPlaces']);

        Route::post('/places/fetch-kavre', [KavrepalanchowkPlacesController::class, 'fetchKavrePopularPlaces']);

        Route::post('/places/fetch-nuwakot', [NuwakotPlacesController::class, 'fetchNuwakotPopularPlaces']);
    });

    Route::post('/logout', [LoginController::class, 'logout']);

    Route::get('/places/popular', [PopularPlacesController::class, 'fetchFromDb']);

    Route::get('/places/popular/{id}', [PopularPlacesController::class, 'fetchById']);

    Route::post('/categories', [CategoryController::class, 'store']);

    Route::get('/places-by-category', [UserCategoryController::class, 'fetchByUserCategories']);

    Route::get('/places-by-category/{id}', [UserCategoryController::class, 'fetchById']);

    Route::get('/places/category', [CategoryPlacesController::class, 'fetchByCategory']);

    Route::get('/places/category/{id}', [CategoryPlacesController::class, 'fetchById']);

    Route::post('/location-reviews', [LocationReviewController::class, 'store']);

    Route::post('/location-images/{locationId}', [LocationImageController::class, 'store']);

    Route::post('/favourites', [FavouriteLocationController::class, 'add']);

    Route::get('/favourites', [FavouriteLocationController::class, 'index']);

    Route::get('/location-reviews/{locationId}', [FetchLocationReviewController::class, 'fetchByLocationId']);

    Route::get('/places/search', [SearchPlacesController::class, 'search']);

    Route::get('/places/search-history', [SearchPlacesController::class, 'fetchSearchHistory']);

    Route::patch('/activity-status', [UserActivityStatusController::class, 'updateActivityStatus']);
    Route::post('/user-location', [UserLocationController::class, 'updateLocation']);
    Route::post('/nearby-users', [NearbyActiveUsersController::class, 'fetchNearbyOnlineUsers']);
    Route::post('/chats/one-on-one', [ChatController::class, 'createOrGetOneOnOne']);
    Route::post('/chats/send', [ChatMessageController::class, 'store']);
      Route::get('/chats/{chatId}', [ChatMessageController::class, 'fetchMessages']);

});
