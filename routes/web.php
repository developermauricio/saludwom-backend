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
//    return view('mails.activation-email');
    return new \App\Mail\AccountActivation('oamsoamsoamsoas', 'Maria');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
