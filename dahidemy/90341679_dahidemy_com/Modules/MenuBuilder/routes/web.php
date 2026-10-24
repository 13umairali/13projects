<?php

use Illuminate\Support\Facades\Route;
use Modules\MenuBuilder\Http\Controllers\WEB\MenuBuilderController;

Route::group(['middleware' => ['auth'], 'prefix' => 'custom-menu', 'as' => 'menubuilder.'], function () {
    Route::get('/', [MenuBuilderController::class, 'index'])->name('index');
    Route::get('create', [MenuBuilderController::class, 'create'])->name('create');
    Route::post('/', [MenuBuilderController::class, 'store'])->name('store');
    Route::get('{id}/edit', [MenuBuilderController::class, 'edit'])->name('edit');
    Route::put('{id}', [MenuBuilderController::class, 'update'])->name('update');
    Route::delete('{id}', [MenuBuilderController::class, 'destroy'])->name('destroy');
    Route::patch('{id}/toggle-status', [MenuBuilderController::class, 'toggleStatus'])->name('toggle-status');
    Route::patch('{id}/move-up', [MenuBuilderController::class, 'moveUp'])->name('move-up');
    Route::patch('{id}/move-down', [MenuBuilderController::class, 'moveDown'])->name('move-down');
    Route::post('reset-to-defaults', [MenuBuilderController::class, 'resetToDefaults'])->name('reset-to-defaults');
});
