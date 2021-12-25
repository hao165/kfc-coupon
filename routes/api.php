<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CommentController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::name('api.')->group(function () {
    Route::prefix('comments')->name('comments.')->group(function () {
        Route::get('', [CommentController::class, 'index'])->name('index');
        //Route::get('{comment}', [CommentController::class, 'show'])->name('show');
        Route::middleware(['auth:sanctum', 'verified'])->post('', [CommentController::class, 'store'])->name('store');
        //Route::middleware(['auth:sanctum', 'verified'])->put('{comment}', [CommentController::class, 'update'])->name('update');
        //Route::middleware(['auth:sanctum', 'verified'])->delete('{comment}', [CommentController::class, 'destroy'])->name('destroy');
    });
});
