<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use \App\Http\Controllers\Controller;
use \App\Http\Controllers\Auth\LoginController;
use \App\Http\Controllers\Api\V1\PlanController;
use \App\Http\Controllers\Auth\RegisterController;
use \App\Http\Controllers\Api\V1\PatientController;
use \App\Http\Controllers\Api\V1\CheckoutController;
use \App\Http\Controllers\Auth\VerificationController;
use \App\Http\Controllers\Auth\ResetPasswordController;
use \App\Http\Controllers\Auth\ForgotPasswordController;


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

Route::group(['middleware' => ['guest:api']], function () {

    Route::post('login', [LoginController::class, 'login']);
    Route::post('register', [RegisterController::class, 'register'])->name('register');
    Route::post('verification/verify/{user}', [VerificationController::class, 'verify'])->name('verification.verify');
    Route::post('verification/resend', [VerificationController::class, 'resend'])->name('verification.resend');
    Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail']);
    Route::post('password/reset', [ResetPasswordController::class, 'reset']);
//    Route::get('activate-account/{token}', [ActivatedController::class, 'activateAccount'])->name('api.v1.activate.account'); // Activar cuenta del usuari

});
/*=============================================
      RUTAS CON AUTENTICACIÓN JWT
 =============================================*/
Route::group(['middleware' => ['auth:api']], function () {
    /*Cerrar sesión*/
    Route::post('logout', [LoginController::class, 'logout']);
    /*Obtener el usuario autenticado*/
    Route::get('user', [LoginController::class, 'user']);


    /*=============================================
      RUTAS PARA LOS PLANES
     =============================================*/
    Route::get('/get-plans', [\App\Http\Controllers\Api\V1\PlanController::class, 'getPlans'])->name('get.plans'); /*Obtenemos todos los planes*/
});

Route::get('get-countries', [Controller::class, 'countries'])->name('get.all.countries');
Route::get('get-cities-from-country/{country}', [Controller::class, 'citiesFromCountry'])->name('get.city.from.country');
Route::get('/verify-email-user/{email}', [Controller::class, 'validateEmailApi'])->name('get.validate.email');

/*=============================================
      RUTAS PARA EL PACIENTE
 =============================================*/
Route::post('register-patient', [PatientController::class, 'register'])->name('register.patient');


/*=============================================
      RUTAS PARA EL PROCESO DE PAGO
 =============================================*/
Route::get('checkout/intent', [CheckoutController::class, 'intentStripe'])->name('get.all.countries');
Route::post('checkout/payment', [CheckoutController::class, 'paymentStripe'])->name('stripe.payment');
