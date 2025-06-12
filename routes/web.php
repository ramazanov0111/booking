<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::prefix('proger')->group(function (){
        Route::prefix('users')->group(function (){
            Route::get('/', function () {
                return Inertia::render('Admin/UserList');
            })->name('users.list');
            Route::get('/edit/{user}', function () {
                return Inertia::render('Admin/UserForm');
            })->name('users.edit');
            Route::get('/create', function () {
                return Inertia::render('Admin/UserForm');
            })->name('users.create');
        });
        Route::prefix('rooms')->group(function (){
            Route::get('/', function () {
                return Inertia::render('Admin/RoomIndex');
            })->name('rooms.list');
            Route::get('/edit/{room}', function () {
                return Inertia::render('Admin/RoomShow');
            })->name('rooms.edit');
            Route::get('/create', function () {
                return Inertia::render('Admin/RoomShow');
            })->name('rooms.create');
        });
        Route::prefix('prices')->group(function (){
            Route::get('/', function () {
                return Inertia::render('Admin/PriceList');
            })->name('prices.list');
            Route::get('/edit/{price}', function () {
                return Inertia::render('Admin/PriceForm');
            })->name('prices.edit');
            Route::get('/create', function () {
                return Inertia::render('Admin/PriceForm');
            })->name('prices.create');
        });
        Route::prefix('booking')->group(function (){
            Route::get('/', function () {
                return Inertia::render('Admin/BookingList');
            })->name('booking.list');
            Route::get('/edit/{booking}', function () {
                return Inertia::render('Admin/BookingForm');
            })->name('booking.edit');
            Route::get('/create', function () {
                return Inertia::render('Admin/BookingForm');
            })->name('booking.create');
        });
        Route::prefix('reviews')->group(function (){
            Route::get('/', function () {
                return Inertia::render('Admin/ReviewList');
            })->name('reviews.list');
        });
        Route::prefix('blocked_dates')->group(function (){
            Route::get('/', function () {
                return Inertia::render('Admin/BlockedDateList');
            })->name('blocked_dates.list');
            Route::get('/edit/{blocked_date}', function () {
                return Inertia::render('Admin/BlockedDateForm');
            })->name('blocked_dates.edit');
            Route::get('/create', function () {
                return Inertia::render('Admin/BlockedDateForm');
            })->name('blocked_dates.create');
        });
    });
});

//Route::get('/', function () {
//    return Inertia::render('Welcome', [
//        'canLogin' => Route::has('login'),
//        'canRegister' => Route::has('register'),
//        'laravelVersion' => Application::VERSION,
//        'phpVersion' => PHP_VERSION,
//    ]);
//});

Route::get('/', function () {
    return Inertia::render('AboutPage');
})->name('main');

Route::get('/booking/{userId}', function ($userId) {
    return Inertia::render('BookingPage',
        ['userId' => $userId]
    );
})->name('booking');

Route::get('/rooms', function () {
    return Inertia::render('RoomsPage');
})->name('rooms');

Route::get('/rooms/{room}', function () {
    return Inertia::render('RoomShow');
})->name('room.show');

Route::get('/profile', function () {
    return Inertia::render('Profile/Guest');
})->name('guest.profile');
