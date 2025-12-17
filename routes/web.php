<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ToolController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/public-projects', [ProjectController::class, 'publicIndex'])->name('public.index');
Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('projects', ProjectController::class);
    Route::post('/projects/{project}/like', [ProjectController::class, 'like'])->name('projects.like');
    Route::post('/projects/{project}/complain', [ComplaintController::class, 'store'])->name('projects.complain');
    Route::delete('/images/{image}', [ImageController::class, 'destroy'])->name('images.destroy');
    Route::post('/images/{image}/set-as-cover', [ImageController::class, 'setAsCover'])->name('images.setAsCover');

    Route::resource('tools', ToolController::class)->only(['index', 'store', 'destroy']);
    Route::get('/tools/search', [ToolController::class, 'search'])->name('tools.search');
    Route::patch('/tools/{tool}/toggle-approval', [ToolController::class, 'toggleApproval'])->name('tools.toggleApproval');

    Route::resource('categories', CategoryController::class);

    // Admin Panel Routes
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/users', [AdminController::class, 'usersIndex'])->name('users.index');
        Route::patch('/users/{user}/toggle-block', [AdminController::class, 'toggleBlock'])->name('users.toggleBlock');
        Route::patch('/users/{user}/toggle-admin', [AdminController::class, 'toggleAdmin'])->name('users.toggleAdmin');
        
        Route::get('/projects', [AdminController::class, 'projectsIndex'])->name('projects.index');
        Route::patch('/projects/{project}/toggle-block', [AdminController::class, 'toggleProjectBlock'])->name('projects.toggleBlock');
        Route::get('/projects/{project}/complaints', [AdminController::class, 'showProjectComplaints'])->name('projects.complaints');
        Route::patch('/complaints/{complaint}/approve', [AdminController::class, 'approveComplaint'])->name('complaints.approve');
        Route::patch('/complaints/{complaint}/decline', [AdminController::class, 'declineComplaint'])->name('complaints.decline');
    });
});

require __DIR__.'/auth.php';