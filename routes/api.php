<?php

use App\Http\Controllers\Admin\AdminBlockedDateController;
use App\Http\Controllers\Admin\AdminConfigController;
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
    Route::apiResource('configs', AdminConfigController::class);

    Route::get('actual_price', [AdminPriceController::class, 'actualPrice']);

    Route::get('bookings/{room}', [AdminBookingController::class, 'getBookingsByRoom'])->name('bookings.by_room');

    Route::get('enabled_rooms', [AdminRoomController::class, 'enabledRooms'])->name('rooms.enabled');

    Route::patch('review/publish/{review}', [AdminReviewController::class, 'publish'])->name('review.publish');

    Route::post('upload_file/{room}', [AdminRoomController::class, 'uploadFile'])->name('room.upload_file');
    Route::delete('rooms/photo/{room}', [AdminRoomController::class, 'deletePhoto'])->name('rooms_photo.destroy');

    Route::post('upload_gallery/{room}', [AdminRoomController::class, 'uploadGalleryFiles'])->name('room.upload_gallery');
    Route::delete('rooms/gallery/{image}', [AdminRoomController::class, 'deleteGalleryPhoto'])->name('rooms_gallery.destroy');

})->middleware('auth:sanctum');


Route::get('/rooms', [SpaBaseController::class, 'roomList'])->name('api.rooms.list');
Route::get('/price/dates', [SpaBaseController::class, 'priceByDates'])->name('price.by_dates');
Route::get('/blocked_dates/{room}', [SpaBaseController::class, 'getBlockedDatesByRoom'])->name('blocked_dates.by_room');
Route::get('/bookings/{room}', [SpaBaseController::class, 'getBookingDatesByRoom'])->name('booking_dates.by_room');
Route::get('/bookings', [SpaBaseController::class, 'getBookingsForUser'])->name('bookings');
Route::patch('/cancel/{booking}', [SpaBaseController::class, 'cancelBooking'])->name('booking.cancel');
Route::get('/reviews', [SpaBaseController::class, 'getReviews'])->name('reviews');
Route::get('amenities', [SpaBaseController::class, 'getAmenities'])->name('amenities');
Route::get('contacts', [SpaBaseController::class, 'getContacts'])->name('contacts');
