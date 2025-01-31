<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\AuthController;
use App\Http\Controllers\api\BookController;
use App\Http\Controllers\api\BorrowedBookController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('login', [AuthController::class, 'authenticate']);
Route::post('register', [AuthController::class, 'register']);

Route::group(['middleware' => ['jwt.verify', 'throttle:60,1']], function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh_token', [AuthController::class, 'refreshToken']);
    // Admin
    Route::get('get_users', [AuthController::class, 'getAllUsers']);
    Route::post('book-store', [BookController::class, 'store']);
    Route::put('book-update/{id}', [BookController::class, 'update']);
    Route::delete('book-delete/{id}', [BookController::class, 'destroy']);
    Route::get('get-books', [BookController::class, 'getBooks']);

    // User 
    Route::post('borrowed-book/{book_id}', [BorrowedBookController::class, 'borrowedBook']);
    Route::post('return-book/{book_id}', [BorrowedBookController::class, 'returnBook']);
});
