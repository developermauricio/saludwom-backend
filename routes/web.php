<?php

use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\Api\V1\StripeWebHookController;

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

Route::post('stripe/webhook', [StripeWebHookController::class, 'handleWebHook']);


Route::get('/', function () {
    return view('welcome');
});
Route::get('/mail', function () {

    dd(ucwords(\Jenssegers\Date\Date::parse('2023-01-09 13:23:01')->locale('es')->format('F d Y')));
////    return view('mails.activation-email');
//    return \Illuminate\Support\Facades\Notification::send(new \App\Notifications\SendInvoiceNotification('Mauro', 'so', 'sp', 's'));
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
