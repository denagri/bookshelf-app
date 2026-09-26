<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BooksController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReadingPlanController;
use App\Http\Controllers\NotificationController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [BooksController::class, 'index'])
    ->name('books.index');

Route::middleware('auth')->group(function(){
    Route::get('/books/create', [BooksController::class, 'create'])
        ->name('books.create');
    Route::post('/books', [BooksController::class, 'store'])
        ->name('books.store');
    Route::get('/books/{book}/edit', [BooksController::class, 'edit'])
        ->name('books.edit');
    Route::put('/books/{book}', [BooksController::class, 'update'])
        ->name('books.update');
    Route::delete('/books/{book}', [BooksController::class, 'destroy'])
        ->name('books.destroy');
    Route::get('/favorites', [FavoriteController::class, 'index'])
        ->name('favorites.index');
    Route::post('/books/{book}/favorite', [FavoriteController::class, 'toggle'])
        ->name('favorites.toggle');
    Route::resource('genres', GenreController::class);
    Route::post('/books/{book}/reviews', [ReviewController::class, 'store'])
        ->name('reviews.store');
    Route::get('/reviews/{review}/edit', [ReviewController::class, 'edit'])
        ->name('reviews.edit');
    Route::put('/reviews/{review}', [ReviewController::class, 'update'])
        ->name('reviews.update');
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])
        ->name('reviews.destroy');
    Route::post('/reviews/{review}/like', [ReviewController::class, 'like'])
        ->name('reviews.like');
    Route::get('/reports', [ReportController::class, 'index'])
        ->name('reports.index');
    Route::get('/notifications', [ReportController::class, 'notifications'])
        ->name('notifications.index');
    Route::post('/notifications/{id}/read', [ReportController::class, 'readNotification'])
        ->name('notifications.read');
    Route::post('/reading-plans/{reading_plan}/complete', [ReadingPlanController::class, 'complete'])
        ->name('reading-plans.complete');
    Route::resource('reading-plans', ReadingPlanController::class)->except(['show']);
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
});

Route::get('/books/{book}', [BooksController::class, 'show'])
    ->name('books.show');
Route::get('/ranking', [BooksController::class, 'ranking'])
    ->name('ranking.index');