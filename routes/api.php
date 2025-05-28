<?php

use App\Http\Controllers\Admin\AdminBlockedDateController;
use App\Http\Controllers\Admin\AdminPriceController;
use App\Http\Controllers\Admin\AdminRoomController;
use App\Http\Controllers\Admin\AdminBookingController;
use App\Http\Controllers\Admin\AdminReviewController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Spa\SpaBaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('proger')->group(function (){
    Route::get('/me', function (Request $request) {
        return $request->user();
    });
    Route::apiResource('users', AdminUserController::class);
    Route::apiResource('rooms', AdminRoomController::class);
    Route::apiResource('prices', AdminPriceController::class);
    Route::apiResource('booking', AdminBookingController::class);
    Route::apiResource('reviews', AdminReviewController::class);
    Route::apiResource('blocked_dates', AdminBlockedDateController::class);

    Route::get('actual_price', [AdminPriceController::class, 'actualPrice']);

    Route::patch('review/publish/{review}', [AdminReviewController::class, 'publish'])->name('review.publish');
    Route::delete('rooms/photo/{room}', [AdminRoomController::class, 'deletePhoto'])->name('rooms_photo.destroy');

})->middleware('auth:sanctum');


Route::get('/rooms', [SpaBaseController::class, 'roomList'])->name('api.rooms.list');
Route::get('/price/dates', [SpaBaseController::class, 'priceByDates'])->name('price.by_dates');

