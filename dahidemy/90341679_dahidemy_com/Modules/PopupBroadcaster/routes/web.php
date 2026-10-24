<?php

use Illuminate\Support\Facades\Route;
use Modules\PopupBroadcaster\Http\Controllers\WEB\PopupBroadcasterController;

Route::group(['middleware' => ['auth'], 'prefix' => 'popups', 'as' => 'popupbroadcaster.'], function () {
    Route::get('/', [PopupBroadcasterController::class, 'index'])->name('index');
    Route::get('create', [PopupBroadcasterController::class, 'create'])->name('create');
    Route::post('/', [PopupBroadcasterController::class, 'store'])->name('store');
    Route::get('{id}/edit', [PopupBroadcasterController::class, 'edit'])->name('edit');
    Route::put('{id}', [PopupBroadcasterController::class, 'update'])->name('update');
    Route::delete('{id}', [PopupBroadcasterController::class, 'destroy'])->name('destroy');
    Route::patch('{id}/toggle-status', [PopupBroadcasterController::class, 'toggleStatus'])->name('toggle-status');
});
