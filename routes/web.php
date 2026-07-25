<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BooksController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\GenreController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// トップページ（書籍一覧）
Route::get('/', [BooksController::class, 'index'])
    ->name('books.index');

Route::middleware('auth')->group(function(){
    // 書籍登録
    Route::get('/books/create', [BooksController::class, 'create'])
    ->name('books.create');
    Route::post('/books', [BooksController::class, 'store'])
    ->name('books.store');

     // 本の「編集」「更新」「削除」ルート
    Route::get('/books/{book}/edit', [BooksController::class, 'edit'])
    ->name('books.edit');
    Route::put('/books/{book}', [BooksController::class, 'update'])
    ->name('books.update');
    Route::delete('/books/{book}', [BooksController::class, 'destroy'])
    ->name('books.destroy');

    // お気に入りトグル
    Route::get('/favorites', [FavoriteController::class, 'index'])
    ->name('favorites.index');
    Route::post('/books/{book}/favorite', [FavoriteController::class, 'toggle'])
    ->name('favorites.toggle');

    Route::resource('genres', GenreController::class);

    // レビューの投稿・編集画面・更新・削除
    Route::post('/books/{book}/reviews', [ReviewController::class, 'store'])
    ->name('reviews.store');
    Route::get('/reviews/{review}/edit', [ReviewController::class, 'edit'])
    ->name('reviews.edit');
    Route::put('/reviews/{review}', [ReviewController::class, 'update'])
    ->name('reviews.update');
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])
    ->name('reviews.destroy');

    // レビューのいいね
    Route::post('/reviews/{review}/like', [ReviewController::class, 'like'])
    ->name('reviews.like');
});

// 書籍詳細
Route::get('/books/{book}', [BooksController::class, 'show'])
    ->name('books.show');

Route::get('/ranking', [BooksController::class, 'ranking'])
->name('ranking.index');
