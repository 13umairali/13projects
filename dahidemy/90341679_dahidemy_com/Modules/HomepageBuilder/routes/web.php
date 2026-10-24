<?php

use Illuminate\Support\Facades\Route;
use Modules\HomepageBuilder\Http\Controllers\WEB\DirectorMessageController;
use Modules\HomepageBuilder\Http\Controllers\WEB\HomepageSectionController;

Route::group(['middleware' => ['auth'], 'prefix' => 'admin/homepage', 'as' => 'homepagebuilder.admin.'], function () {
    Route::get('sections', [HomepageSectionController::class, 'index'])->name('sections.index');
    Route::patch('sections/{id}/toggle', [HomepageSectionController::class, 'toggle'])->name('sections.toggle');
    Route::patch('sections/{id}/move-up', [HomepageSectionController::class, 'moveUp'])->name('sections.move-up');
    Route::patch('sections/{id}/move-down', [HomepageSectionController::class, 'moveDown'])->name('sections.move-down');

    Route::get('director-message', [DirectorMessageController::class, 'edit'])->name('director-message.edit');
    Route::post('director-message', [DirectorMessageController::class, 'update'])->name('director-message.update');
});
