<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SpeedBumpController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
})->name('home');

// Locale switcher (updates session)
Route::get('/locale/{lang}', function ($lang) {
    $allowed = ['en', 'ar'];
    if (in_array($lang, $allowed)) {
        session(['locale' => $lang]);
    }
    return redirect()->back();
})->name('locale.switch');

// Auth Routes
Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Speed Bumps
    Route::get('/bumps', [SpeedBumpController::class, 'index'])->name('bumps.index');
    Route::get('/bumps/create', [SpeedBumpController::class, 'create'])->name('bumps.create');
    Route::post('/bumps', [SpeedBumpController::class, 'store'])->name('bumps.store');
    Route::get('/map', [SpeedBumpController::class, 'map'])->name('bumps.map');

    // Profile
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::post('/profile/update', [ProfileController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Admin Routes
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/users', [AdminController::class, 'users'])->name('users');
        Route::get('/bumps', [AdminController::class, 'bumps'])->name('bumps');
        Route::get('/reports', [AdminController::class, 'reports'])->name('reports');
        Route::get('/predictions', [AdminController::class, 'predictions'])->name('predictions');
        Route::get('/testing', [AdminController::class, 'testing'])->name('testing');
        Route::post('/bumps/{bump}/approve', [AdminController::class, 'approveBump'])->name('bumps.approve');
        Route::post('/bumps/{bump}/reject', [AdminController::class, 'rejectBump'])->name('bumps.reject');
        Route::post('/predictions/{prediction}/convert', [AdminController::class, 'convertPrediction'])->name('predictions.convert');
    });
});
