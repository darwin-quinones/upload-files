<?php

// controller
use App\Http\Controllers\FileController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;



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
// FIles routes
Route::get('file-upload-liquidaciones', [FileController::class, 'index'])->name('file.uploadLiquidaciones');
//Route::post('file-upload', [FileController::class, 'store'])->name('file.upload.store');
Route::post('/file-upload-liquidaciones', [FileController::class, 'fileUploadLiquidaciones']);

Route::get('file-upload-oyrmi', [FileController::class, 'uploadOYMRI'])->name('file.uploadOYMRI');
Route::post('file-upload-oyrmi', [FileController::class, 'FileUploadOYMRI']);

// Route::post('file-progress-bar', [FileController::class, 'fileUploadProgressBar'])->name('file.progress.bar.upload');
Route::get('/file-upload-comercializadores', [FileController::class, 'uploadComercializadores'])->name('file.uploadComercializadores');
Route::post('/file-upload-comercializadores', [FileController::class, 'FileUploadComercializadores']);




Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
