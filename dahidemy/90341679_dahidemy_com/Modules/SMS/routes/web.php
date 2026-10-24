<?php

use Illuminate\Support\Facades\Route;
use Modules\SMS\Http\Controllers\WEB\PhoneBookCategoryController;
use Modules\SMS\Http\Controllers\WEB\PhoneBookController;
use Modules\SMS\Http\Controllers\WEB\SmsController;
use Modules\SMS\Http\Controllers\WEB\SmsPurchaseController;
use Modules\SMS\Http\Controllers\WEB\SmsTemplateController;

Route::group(['middleware' => ['auth']], function () {
    Route::resource('sms-template', SmsTemplateController::class);
    Route::resource('phone-book-category', PhoneBookCategoryController::class);
    Route::resource('phone-book', PhoneBookController::class);
    Route::resource('sms-purchase', SmsPurchaseController::class);
    Route::get('sms-compose', [SmsController::class, 'index'])->name('sms-compose');
    Route::post('sms-send', [SmsController::class, 'send'])->name('sms.compose');
    Route::match(['get', 'post'], 'send-sms-report', [SmsController::class, 'sentSMSReport'])->name('send-sms-report.index');
    Route::get('users-get_users/{user_type}', [SmsController::class, 'get_users']);
});
