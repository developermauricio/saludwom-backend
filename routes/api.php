<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use \App\Http\Controllers\Controller;
use \App\Http\Controllers\Api\V1\Auth\LoginController;

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

Route::group(['middleware' => 'api', 'prefix' => 'auth'], function () {

    Route::post('login', [LoginController::class, 'login']);

});
/*=============================================
      RUTAS CON AUTENTICACIÓN JWT
 =============================================*/
Route::group(['middleware' => ['jwt.verify']], function () {
    /*Obtener el usuario autenticado*/

    Route::get('user', [LoginController::class, 'user']);
});

Route::get('get-countries', [Controller::class, 'countries'])->name('get.all.countries');
Route::get('get-cities-from-country/{country}', [Controller::class, 'citiesFromCountry'])->name('get.city.from.country');
Route::get('/verify-email-user/{email}', [Controller::class, 'validateEmail'])->name('get.validate.email');
