<?php

use Illuminate\Support\Facades\Route;
use Modules\LayoutCert\Http\Controllers\API\LayoutCertController;

Route::middleware('auth:api')->group(function () {
    Route::get('layout-certificates', [LayoutCertController::class, 'index']);
});
