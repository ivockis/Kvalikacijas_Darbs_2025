<?php

use App\Http\Controllers\ImageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('projects', ProjectController::class);
    Route::post('/projects/{project}/like', [ProjectController::class, 'like'])->name('projects.like');
    Route::delete('/images/{image}', [ImageController::class, 'destroy'])->name('images.destroy');
    Route::post('/images/{image}/set-as-cover', [ImageController::class, 'setAsCover'])->name('images.setAsCover');
});

require __DIR__.'/auth.php';
