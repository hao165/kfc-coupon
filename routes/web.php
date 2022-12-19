<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\CollectController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CrawlerController;
use App\Http\Controllers\TrackController;
use App\Services\GitHook;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });

Route::name('error.')->group(function () {
    Route::view('403', 'errors.403')->name('403');
    Route::view('410', 'errors.410')->name('410');
    Route::view('500', 'errors.500')->name('500');
});

Route::middleware(['auth:sanctum', 'verified'])->get('/SuperiZO', function () {
    return view('dashboard');
})->name('dashboard');

Route::get('/login/{provider}', [LoginController::class, 'redirectToProvider'])
    ->name('social.login');
Route::get('/login/{provider}/callback', [LoginController::class, 'handleProviderCallback'])
    ->name('social.callback');

Route::name('coupons.')->group(function () {
    Route::get('/', [CouponController::class, 'index'])->name('index');
    Route::redirect('coupons', '/');
    Route::get('coupons/expired', [CouponController::class, 'expired'])->name('expired');
    Route::middleware(['auth:sanctum', 'verified'])->get('coupons/create', [CouponController::class, 'create'])->name('create');
    Route::middleware(['auth:sanctum', 'verified'])->get('coupons/{slug}/edit', [CouponController::class, 'edit'])->name('edit');
    Route::middleware(['auth:sanctum', 'verified'])->post('coupons', [CouponController::class, 'store'])->name('store');
    Route::middleware(['auth:sanctum', 'verified'])->put('coupons/{slug}', [CouponController::class, 'update'])->name('update');
    Route::middleware(['auth:sanctum', 'verified'])->delete('coupons/{slug}', [CouponController::class, 'destroy'])->name('destroy');
    Route::middleware(['auth:sanctum', 'verified'])->get('coupons/{id}/verify/{type}', [CouponController::class, 'verify'])->name('verify');

    Route::get('coupons/view-count', [CouponController::class, 'viewCount'])->name('view_count');
    // Note: coupons/{slug}不可高於coupons/expired
    Route::get('coupons/{slug}', [CouponController::class, 'show'])->name('show');
});

Route::prefix('chat')->name('chat.')->group(function () {
    Route::get('', function () {
        return redirect()->route('chat.index', 1);
    });
    Route::get('{id}', [CommentController::class, 'index'])->name('index');
});

Route::prefix('collect')->name('collect.')->group(function () {
    Route::middleware(['auth:sanctum', 'verified'])->get('', [CollectController::class, 'index'])->name('index');
    Route::middleware(['auth:sanctum', 'verified'])->post('', [CollectController::class, 'update'])->name('update');
    Route::get('rank', [CollectController::class, 'rank'])->name('rank');
});

Route::prefix('img')->name('img.')->group(function () {
    Route::get('{img}', function ($img) {
        return redirect('https://i.imgur.com/'.$img);
    });
    Route::post('imgur', [CouponController::class, 'imgur'])->name('imgur');
});

Route::prefix('member')->name('member.')->group(function () {
    Route::middleware(['auth:sanctum', 'verified'])->get('edit', [UserController::class, 'edit'])->name('edit');
    Route::middleware(['auth:sanctum', 'verified'])->put('edit', [UserController::class, 'update'])->name('update');
});

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware(['auth:sanctum', 'verified'])->get('pending-list', [CouponController::class, 'pendingList'])->name('pending_list');
    Route::middleware(['auth:sanctum', 'verified'])->prefix('crawler')->name('crawler.')->group(function () {
        Route::get('post-list', [CrawlerController::class, 'postList'])->name('post_list');
        Route::get('item-list/{id}', [CrawlerController::class, 'itemList'])->name('item_list');
        Route::post('push', [CrawlerController::class, 'itemPush'])->name('item_push');
        Route::post('item-check', [CrawlerController::class, 'itemCheck'])->name('item_check');
        Route::post('store', [CrawlerController::class, 'store'])->name('store');
        Route::post('switch-status', [CrawlerController::class, 'switchStatus'])->name('switch_status');
    });
    Route::middleware(['auth:sanctum', 'verified'])->prefix('track')->name('track.')->group(function () {
        Route::get('init', [TrackController::class, 'init'])->name('init');
        Route::get('list', [TrackController::class, 'list'])->name('list');
        Route::get('item/{id}', [TrackController::class, 'item'])->name('item');
        Route::post('store', [TrackController::class, 'store'])->name('store');
    });
});

Route::get('line-notify-callback', [TrackController::class, 'callback'])->name('line_notify_callback');
Route::redirect('/qa', '/', 301)->name('qa');

Route::post('git-hook', [GitHook::class, 'handle'])->name('git_hook');
