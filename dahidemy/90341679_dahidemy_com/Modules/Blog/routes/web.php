<?php

use Illuminate\Support\Facades\Route;
use Modules\Blog\Http\Controllers\WEB\BlogCategoryController;
use Modules\Blog\Http\Controllers\WEB\BlogPageController;
use Modules\Blog\Http\Controllers\WEB\BlogPostController;

/*
|--------------------------------------------------------------------------
| Admin — manage categories and posts
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => ['auth'], 'prefix' => 'admin/blog', 'as' => 'blog.admin.'], function () {
    Route::resource('categories', BlogCategoryController::class)->except(['show']);

    Route::resource('posts', BlogPostController::class)->except(['show']);
    Route::patch('posts/{id}/toggle-status', [BlogPostController::class, 'toggleStatus'])->name('posts.toggle-status');
});

/*
|--------------------------------------------------------------------------
| Public — blog listing + post detail
|--------------------------------------------------------------------------
*/
Route::prefix('blog')->name('blog-page.')->group(function () {
    Route::get('/', [BlogPageController::class, 'index'])->name('index');
    Route::get('{slug}', [BlogPageController::class, 'show'])->name('show');
});
