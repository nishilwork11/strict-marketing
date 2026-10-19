<?php

use App\Http\Controllers\Admin\EnquiryController;
use App\Http\Controllers\Admin\SessionController;
use App\Http\Controllers\ContactController;
use App\Http\Middleware\RequireAdmin;
use Illuminate\Support\Facades\Route;

// The landing page. Every menu and button link scrolls to a section on it.
Route::view('/', 'home')->name('home');

Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');

// The private admin panel, where enquiries from the contact form are listed
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('login', [SessionController::class, 'create'])->name('login');
    Route::post('login', [SessionController::class, 'store'])->middleware('throttle:10,1')->name('login.store');
    Route::post('logout', [SessionController::class, 'destroy'])->name('logout');

    Route::middleware(RequireAdmin::class)->group(function () {
        Route::get('/', [EnquiryController::class, 'index'])->name('enquiries.index');
        Route::get('enquiries/{id}', [EnquiryController::class, 'show'])->name('enquiries.show');
        Route::patch('enquiries/{id}', [EnquiryController::class, 'update'])->name('enquiries.update');
        Route::delete('enquiries/{id}', [EnquiryController::class, 'destroy'])->name('enquiries.destroy');
    });
});
