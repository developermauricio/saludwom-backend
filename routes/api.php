<?php

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\Controller;
use \App\Http\Controllers\ChatController;
use \App\Http\Controllers\Auth\LoginController;
use \App\Http\Controllers\Api\V1\PlanController;
use \App\Http\Controllers\Api\V1\AdminController;
use \App\Http\Controllers\Api\V1\OrderController;
use \App\Http\Controllers\Api\V1\CouponController;
use \App\Http\Controllers\Api\V1\DoctorController;
use \App\Http\Controllers\Auth\RegisterController;
use \App\Http\Controllers\Api\V1\FolderController;
use \App\Http\Controllers\Api\V1\PatientController;
use \App\Http\Controllers\Api\V1\CheckoutController;
use \App\Http\Controllers\Auth\VerificationController;
use \App\Http\Controllers\Api\V1\TreatmentController;
use \App\Http\Controllers\Api\V1\ValorationController;
use \App\Http\Controllers\Api\V1\NotificationController;
use \App\Http\Controllers\Api\V1\AppointmentController;
use \App\Http\Controllers\Auth\ResetPasswordController;
use \App\Http\Controllers\Auth\ForgotPasswordController;
use \App\Http\Controllers\Api\V1\QuestionnaireController;
use \App\Http\Controllers\Api\V1\SubscriptionsController;
use \App\Http\Controllers\Api\V1\ResourceFolderContentController;


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
      RUTAS PARA LAS SUSCRIPCIONES
     =============================================*/
    Route::get('/get-subscriptions', [SubscriptionsController::class, 'getSubscritions'])->name('get.subscription'); /*Obtenemos todas las suscripciones*/
    Route::get('/get-current-subscription', [SubscriptionsController::class, 'currentSubscrition'])->name('get.current.subscription'); /*Obtenemos todas las suscripciones*/
    Route::get('/get-subscriptions-state/{filter}', [SubscriptionsController::class, 'filterSubscriptions'])->name('get.filter.subscription'); /*Filtramos todas las suscripciones*/
    Route::post('cancel-subscription/{id}', [SubscriptionsController::class, 'cancelSubscription'])->name('cancel.subscription'); /*Cancelar Suscripción*/
    /*=============================================
      RUTAS PARA LAS ORDENES DE COMPRA
     =============================================*/
    Route::get('/get-orders-patient', [OrderController::class, 'getOrdersPatient'])->name('get.orders.patient'); /*Obtenemos todas las ordenes de compra por paciente*/

    /*=============================================
      RUTA PARA VERIFICAR EL TOUR
     =============================================*/
    Route::post('/check-tour-welcome-patient', [Controller::class, 'checkTourPatient'])->name('check.tour.patient'); /*Permite verificar la primera vez que inicio sesion el usuario*/
    /*=============================================
      RUTA PARA VERIFICAR SI TIENE DOCUMENTO
     =============================================*/
    Route::get('/check-document', [Controller::class, 'checkDocument'])->name('check.document.patient'); /*Permite verificar si el usuario tiene documento*/
    /*=============================================
      RUTA PARA LOS TRATAMIENTOS
     =============================================*/
    Route::get('get-treatments', [TreatmentController::class, 'getTreatments'])->name('get.treatment');
    /*=============================================
      RUTA PARA LOS DOCTORES
     =============================================*/
    Route::get('check-schedule-available/{id}', [DoctorController::class, 'scheduleAvailable'])->name('check.schedule.available');
    Route::get('get-count-patients-doctor/{idDoctor}', [DoctorController::class, 'getCountPatients'])->name('get.patients.doctor');
    Route::get('get-valorations-doctor/{dateFilter}', [DoctorController::class, 'getValorations'])->name('get.valorations.objectives.doctor');
    /*=============================================
      RUTA PARA EL ADMIN
     =============================================*/
    Route::get('get-patients-admin', [AdminController::class, 'getPatients'])->name('get.patients.admin');
    /*=============================================
      RUTA PARA LA VALORACIÓN
     =============================================*/
    Route::get('get-valuation/{valuation}', [ValorationController::class, 'getValoration'])->name('get.valoration'); /*Obtenemos todas las suscripciones*/
    Route::get('get-valuations', [ValorationController::class, 'getValorations'])->name('get.valorations'); /*Obtenemos todas las suscripciones*/
    Route::post('create-valoration', [ValorationController::class, 'createValoration'])->name('create.valoration');

    Route::post('update-valoration-objetive/{idValuation}', [ValorationController::class, 'updateValorationObjective'])->name('update.valoration.objective');
    Route::post('remove-archive', [ValorationController::class, 'removeArchive'])->name('remove.archive');

    /*=============================================
      RUTA PARA PACIENTES
     =============================================*/
    Route::get('check-signature', [PatientController::class, 'checkSignature'])->name('check.signature');

    /*=============================================
      RUTA PARA CUPONES
     =============================================*/
    Route::post('apply-coupon', [CouponController::class, 'applyCoupon'])->name('apply.coupon');

    /*=============================================
      RUTAS PARA LAS CITAS
     =============================================*/
    Route::post('cancel-appointment/{idAppointmentValuation}', [AppointmentController::class, 'cancelAppointment'])->name('cancel.appointment');
    Route::post('reschedule-appointment', [AppointmentController::class, 'rescheduleAppointment'])->name('reschedule.appointment');

    /*=============================================
      RUTAS PARA LAS NOTIFICACIONES
     =============================================*/
    Route::get('get-notification-users/{idUser}', [ NotificationController::class, 'getNotifications'])->name('get.notifications');
    Route::post('read-at-notification/{notification}', [ NotificationController::class, 'readAtNotifications'])->name('readAt.notifications');
    Route::post('mark-notifications-as-read', [ NotificationController::class, 'markNotificationAsRead'])->name('mark.notifications.as.read');

    /*=============================================
      RUTA PARA EL CHAT
     =============================================*/
    Route::get('get-messages-valoration/{chatId}', [ChatController::class, 'getMessagesChatValoration'])->name('get.chat.message.valoration');
    Route::post('chat-save', [ChatController::class, 'saveMessage'])->name('save.message.chat');
    Route::post('close-online-chat/{chatId}', [ChatController::class, 'closeOnlineChat'])->name('close.online.chat');
    Route::post('message-read-at/{chatId}', [ChatController::class, 'messageReatAt'])->name('message.read.at');
    Route::get('get-unread-message/{chatId}', [ChatController::class, 'getUnreadMessages'])->name('get.unread.messages');

    /*=============================================
      RUTA PARA EL CUESTIONARIO
     =============================================*/
    Route::get('get-type-questions', [QuestionnaireController::class, 'getTypeQuestions'])->name('get.type.questions');
    Route::get('get-questionnaires', [QuestionnaireController::class, 'getQuestionnaires'])->middleware('questionnaire.permissions')->name('get.questionnaires');
    Route::post('add-questionnaire', [QuestionnaireController::class, 'addQuestionnaire'])->name('add.questionnaire');
    Route::post('update-questionnaire/{id}', [QuestionnaireController::class, 'updateQuestionnaire'])->name('update.questionnaire');
    Route::post('delete-questionnaire/{id}', [QuestionnaireController::class, 'deleteQuestionnaire'])->name('delete.questionnaire');
    Route::post('update-state-questionnaire/{questionnaireId}/{state}', [QuestionnaireController::class, 'updateStateQuestionnaire'])->name('update.state.questionnaire');

    /*=============================================
      RUTA PARA LOS FOLDERS
     =============================================*/
    Route::get('get-folders', [FolderController::class, 'getFolders'])->name('get.folders');
    Route::get('get-files-resource-to-folder/{id}', [ResourceFolderContentController::class, 'getResourceFiles'])->name('get.files.resources.folder');
    Route::post('add-resource-folder', [ResourceFolderContentController::class, 'addResourceFolder'])->name('add.resource.folder');
    Route::post('update-resource-folder/{id}', [ResourceFolderContentController::class, 'updateResourceFolder'])->name('update.resource.folder');
});
Route::post('/upload-files-valuation/{id}/{valutionId}', [ValorationController::class, 'uploadFiles'])->name('upload.file.valuation');
Route::get('get-genders', [Controller::class, 'getGenders'])->name('get.genders');
Route::get('get-timezones', [Controller::class, 'allTimezone'])->name('get.timezones');
Route::get('get-document-types', [Controller::class, 'getDocumentTypes'])->name('get.document.types');
Route::get('get-countries', [Controller::class, 'countries'])->name('get.all.countries');
Route::get('get-cities-from-country/{country}', [Controller::class, 'citiesFromCountry'])->name('get.city.from.country');
Route::get('/verify-email-user/{email}', [Controller::class, 'validateEmailApi'])->name('get.validate.email');

Route::post('send-notification-new-message', [Controller::class, 'testSendEmailMessage']);


/*=============================================
      RUTA PARA LAS FACTURAS
     =============================================*/
Route::get('/download-invoice/{orderId}/{userId}', [OrderController::class, 'downloadInvoice'])->name('download.invoice.user');

/*=============================================
      RUTAS PARA EL PACIENTE
 =============================================*/
Route::post('register-patient', [PatientController::class, 'register'])->name('register.patient');

/*=============================================
      RUTAS PARA LOS PLANES
=============================================*/
Route::get('/get-plans', [PlanController::class, 'getPlans'])->name('get.plans'); /*Obtenemos todos los planes*/
/*=============================================
      RUTAS PARA EL PROCESO DE PAGO
 =============================================*/
Route::get('checkout/intent', [CheckoutController::class, 'intentStripe'])->name('get.all.countries');
Route::post('checkout/payment', [CheckoutController::class, 'paymentStripe'])->name('stripe.payment');
