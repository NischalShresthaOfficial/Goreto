<?php

use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Calls\CallController;
use App\Http\Controllers\Chats\ChatController;
use App\Http\Controllers\Chats\ChatMessageController;
use App\Http\Controllers\Chats\NearbyActiveUsersController;
use App\Http\Controllers\Chats\UserActivityStatusController;
use App\Http\Controllers\Chats\UserLocationController;
use App\Http\Controllers\Groups\GroupController;
use App\Http\Controllers\Payments\PaymentController;
use App\Http\Controllers\Places\CategoryAPIs\CategoryPlacesController;
use App\Http\Controllers\Places\CategoryAPIs\UserCategoryController;
use App\Http\Controllers\Places\FetchAPIs\PopularPlacesController;
use App\Http\Controllers\Places\LocationImages\LocationImageController;
use App\Http\Controllers\Places\SearchAPI\SearchPlacesController;
use App\Http\Controllers\Posts\PostBookmarkController;
use App\Http\Controllers\Posts\PostController;
use App\Http\Controllers\Profile\PasswordController;
use App\Http\Controllers\Profile\ProfilePictureController;
use App\Http\Controllers\Reviews\FetchLocationReviewController;
use App\Http\Controllers\Reviews\LocationReviewController;
use App\Http\Controllers\UserManagement\CategoryController;
use App\Http\Controllers\UserManagement\FavouriteLocationController;
use App\Http\Controllers\Weather\WeatherController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

Route::post('/broadcasting/auth', function (Request $request) {
    $channelName = $request->input('channel_name');
    $socketId = $request->input('socket_id');

    $pusher = Broadcast::driver('pusher')->getPusher();
    $authResponse = $pusher->socket_auth($channelName, $socketId);
    $authResponseArray = json_decode($authResponse, true);

    $authResponseArray['shared_secret'] = config('broadcasting.connections.pusher.options.shared_secret') ?? '';

    Log::info('Custom broadcasting auth response:', $authResponseArray);

    return response()->json($authResponseArray);
});

Route::post('/register', [RegisterController::class, 'register']);
Route::post('/verify-email', [EmailVerificationController::class, 'verifyEmail']);
Route::post('/login', [LoginController::class, 'login']);

Route::post('/reset-password', [NewPasswordController::class, 'store']);
Route::post('/reset-password/token', [PasswordResetLinkController::class, 'store']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
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
    Route::post('/chats/group-chats', [ChatController::class, 'createGroupChat']);
    Route::get('/group-chats/members/{chatId}', [ChatController::class, 'viewMembers']);
    Route::post('/group-chats/add-member/{chatId}', [ChatController::class, 'addMember']);
    Route::post('/group-chats/remove-member/{chatId}', [ChatController::class, 'removeMember']);
    Route::delete('/group-chats/{chatId}', [ChatController::class, 'deleteGroupChat']);
    Route::post('/group-chats/edit/{chatId}', [ChatController::class, 'editGroupChat']);
    Route::get('/group-chats/info/{chatId}', [ChatController::class, 'getGroupChatInfo']);
    Route::post('/chats/send', [ChatMessageController::class, 'store']);
    Route::get('/chats/{chatId}', [ChatMessageController::class, 'fetchMessages']);

    Route::get('/weather/{cityId}', [WeatherController::class, 'fetchAndStoreWeather']);

    Route::post('/call/initiate', [CallController::class, 'initiate']);
    Route::post('/call/offer', [CallController::class, 'sendOffer']);
    Route::post('/call/answer', [CallController::class, 'sendAnswer']);
    Route::post('/call/ice-candidate', [CallController::class, 'sendIceCandidate']);
    Route::post('/call/end', [CallController::class, 'end']);

    Route::post('/posts', [PostController::class, 'store']);
    Route::get('/posts', [PostController::class, 'fetch']);
    Route::get('/posts/mine', [PostController::class, 'fetchMyPosts']);
    Route::get('/posts/{postId}', [PostController::class, 'fetchById']);
    Route::post('/posts/{postId}', [PostController::class, 'editPost']);
    Route::delete('/posts/{postId}', [PostController::class, 'deletePost']);
    Route::post('/post-reviews/{postId}', [PostController::class, 'storeReview']);
    Route::put('/post-reviews/{postId}/{reviewId}', [PostController::class, 'editReview']);
    Route::get('/post-reviews/{postId}', [PostController::class, 'fetchReviews']);
    Route::get('/post-reviews/{postId}/{reviewId}', [PostController::class, 'fetchReviewById']);

    Route::post('/post-bookmarks/{postId}', [PostBookmarkController::class, 'store']);
    Route::get('/post-bookmarks', [PostBookmarkController::class, 'fetchBookmarks']);
    Route::get('/post-bookmarks/{id}', [PostBookmarkController::class, 'fetchById']);

    Route::post('/post-report/{postId}', [PostController::class, 'report']);

    Route::post('/profile-picture', [ProfilePictureController::class, 'store']);
    Route::get('/profile-picture', [ProfilePictureController::class, 'fetch']);
    Route::post('/profile-picture/update', [ProfilePictureController::class, 'update']);
    Route::post('/change-password', [PasswordController::class, 'changePassword']);

    Route::post('/payments', [PaymentController::class, 'createPaymentIntent']);

    Route::post('/groups', [GroupController::class, 'create']);
    Route::post('/group-join/{groupId}', [GroupController::class, 'join']);
    Route::post('/group-locations/{groupId}', [GroupController::class, 'addLocation']);
});
