<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::name('events.')->middleware('api')->prefix('events')->group(function(){
    Route::post('store_leads', 'App\Http\Controllers\EventController@store_leads')->name('api_store_leads');
    Route::post('reinput', 'App\Http\Controllers\EventController@reinput')->name('api_reinput');
    Route::post('import', 'App\Http\Controllers\EventController@import')->name('api_import');
});
