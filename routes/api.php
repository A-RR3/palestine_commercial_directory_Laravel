<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::get('/fcm-access-token', [NotificationController::class, 'generateAccessToken']);
Route::post('/sendNotification', [NotificationController::class, 'sendNotification']);
Route::delete('/remove-device-token', [NotificationController::class, 'removeDeviceToken']);
Route::post('/save-device-token', [NotificationController::class, 'saveDeviceToken']);
Route::post('/update-device-token', [NotificationController::class, 'updateDeviceToken']);


Route::group(['middleware' => ['auth:sanctum','setlocale']],function () {

//User
Route::post('/logout',[AuthController::class,'logout']);
Route::put('/update/{id}',[AuthController::class,'updateUser']);

//Posts
Route::get('/posts/{id}', [PostController::class, 'show']);
Route::post('/posts', [PostController::class, 'store']);
Route::put('/posts/{id}', [PostController::class, 'update']);
Route::delete('/posts/{id}', [PostController::class, 'destroy']);
Route::get('/posts/user/{id}', [PostController::class, 'getPostsWithLikeStatus']);
Route::post('/posts/toggleLike', [PostController::class, 'toggleLike']);

//Users
// Route::post('/users', [UserController::class, 'createUser']);
Route::get('/users', [UserController::class, 'getUsers']);
Route::get('/users/{id}', [UserController::class, 'getSingleUser']);
Route::post('/update', [UserController::class, 'updateUser']);
Route::post('/users/{id}', [UserController::class, 'deactivateUser']);
Route::get('users/search/{name}',[UserController::class,'searchUser']);

//Companies 
Route::get('/companies/{id}', [CompanyController::class, 'getCompanies']);
Route::get('/categories', [CompanyController::class, 'getCategories']);


});


Route::middleware(['setlocale'])->group(function () {
    Route::post('login', [AuthController::class, 'loginUser']);
});
Route::post('/register',[AuthController::class,'register']);
Route::post('/post', [PostController::class, 'uploadVideoTest']);

Route::post('/users', [UserController::class, 'createUser']);
Route::get('/posts', [PostController::class, 'index']);
