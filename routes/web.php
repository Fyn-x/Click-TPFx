<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/leads', 'App\Http\Controllers\HomeController@index')->name('home');
Route::get('/exports', 'App\Http\Controllers\LeadsController@export');

Auth::routes();

Route::name('events.')->prefix('events')->group(function(){
    Route::get('zero-swap-spread-comission', 'App\Http\Controllers\EventController@leads_form')->name('zero_swap_spread_comission');
    Route::get('free-ebook', 'App\Http\Controllers\EventController@leads_form')->name('free_ebook');
    Route::get('trading-micro-lot', 'App\Http\Controllers\EventController@leads_form')->name('trading_micro_lot');
    Route::get('free-swap-300', 'App\Http\Controllers\EventController@leads_form')->name('free_swap_300');
    Route::get('gold-015', 'App\Http\Controllers\EventController@leads_form')->name('gold_015');
    Route::get('free-webinar', 'App\Http\Controllers\EventController@leads_form')->name('free_webinar');
    Route::get('cahsback-reward-20-percent', 'App\Http\Controllers\EventController@leads_form')->name('cashback_reward_20_percent');
    Route::get('acuity-expert-advisor', 'App\Http\Controllers\EventController@leads_form')->name('acuity');
    Route::get('seminar', 'App\Http\Controllers\EventController@leads_form')->name('seminar');
    Route::get('merdeka-tradefest', 'App\Http\Controllers\EventController@leads_form')->name('merdeka-tradefest');
    Route::get('reward-emas', 'App\Http\Controllers\EventController@leads_form')->name('reward-emas');
    Route::get('stars', 'App\Http\Controllers\EventController@leads_form')->name('star');
    Route::get('leads_form_test', 'App\Http\Controllers\EventController@leads_form_test')->name('leads_form_test');
    Route::get('ib', 'App\Http\Controllers\EventController@leads_form')->name('ib');
    Route::get('gebyar-smartphone', 'App\Http\Controllers\EventController@leads_form')->name('gebyar_smartphone');
    Route::get('tpfx-gadget', 'App\Http\Controllers\EventController@leads_form')->name('tpfx_gadget');
    Route::get('big-deal', 'App\Http\Controllers\EventController@leads_form')->name('bigdeal');
    Route::get('thank-you', 'App\Http\Controllers\EventController@thank_you')->name('thank_you');
    Route::get('ebook-basic-thank-you', 'App\Http\Controllers\EventController@thank_you_ebook_basic')->name('thank_you_ebook_basic');

    Route::post('store_leads', 'App\Http\Controllers\EventController@store_leads')->name('store_leads');
    Route::view('test', 'events.test');
});

Route::name('freeholiday.')->prefix('free-holiday')->group(function(){
    Route::get('enrollment', 'App\Http\Controllers\EndYearController@enroll_create')->name('enroll_create');
    Route::post('enrollment_store', 'App\Http\Controllers\EndYearController@enroll_store')->name('enroll_store');
    Route::get('claim', 'App\Http\Controllers\EndYearController@claim_create')->name('claim_create');
    Route::post('claim_store', 'App\Http\Controllers\EndYearController@claim_store')->name('claim_store');
});

Route::name('ftd.')->prefix('ftd')->group(function(){
    Route::get('', 'App\Http\Controllers\FTDController@create')->name('create');
    Route::post('store', 'App\Http\Controllers\FTDController@store')->name('store');
    Route::get('check-leads', 'App\Http\Controllers\FTDController@check_leads')->middleware('auth')->name('check_leads');
    Route::post('check-leads-store', 'App\Http\Controllers\FTDController@check_leads_store')->middleware('auth')->name('check_leads_store');
    Route::get('check-all-leads', 'App\Http\Controllers\FTDController@check_all_leads')->middleware('auth')->name('check_all_leads');
    Route::post('check-all-leads-store', 'App\Http\Controllers\FTDController@check_all_leads_store')->middleware('auth')->name('check_all_leads_store');
    Route::get('check-last-trade', 'App\Http\Controllers\FTDController@check_last_trade')->middleware('auth')->name('check_last_trade');
    Route::post('check-last-trade-store', 'App\Http\Controllers\FTDController@check_last_trade_store')->middleware('auth')->name('check_last_trade_store');
});

Route::view('/trading-central', 'tc.index');

Route::name('leads.')->middleware('auth')->prefix('leads')->group(function(){
    Route::get('', 'App\Http\Controllers\LeadsController@getLeads')->name('getLeads');
    Route::get('upload-leads', 'App\Http\Controllers\UploadLeadsController@index')->name('upload_leads_view');
    Route::post('upload_leads', 'App\Http\Controllers\UploadLeadsController@upload_leads')->name('upload_leads');
});

Route::name('qontak.')->prefix('qontak')->group(function(){
    Route::post('webhook-message', 'App\Http\Controllers\QontakController@qontak_webhook_message')->name('qontak_webhook_message');
});
