<?php

use App\Http\Controllers\GoogleAccountController;
use App\Http\Controllers\GoogleColorsController;
use App\Http\Controllers\GoogleCredentialsController;
use App\Http\Controllers\GoogleEventsController;
use App\Http\Controllers\PassportAuthController;
use App\Http\Controllers\UserController;
use App\Models\GoogleAccount;
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

Route::post('/register', [PassportAuthController::class, 'register']);
Route::post('/login', [PassportAuthController::class, 'login']);

/** Login Oauth */
Route::get('google/oauth', [GoogleAccountController::class, 'getAuth']);
Route::post('google/auth/login', [GoogleAccountController::class, 'getAuth']);
Route::get('google/login/url', [GoogleAccountController::class, 'getAuthUrl']);

Route::post('google/events/webhook', [GoogleEventsController::class, 'webhookEvent']);

Route::group(['middleware' => 'auth:api'], function () {
    /** Credenciais */
    Route::post('google/credentials/register-key', [GoogleCredentialsController::class, 'createCredential']);
    Route::get('google/credentials/list-keys', [GoogleCredentialsController::class, 'listCredentials']);
    Route::put('google/credentials/update-key', [GoogleCredentialsController::class, 'updateCredential']);
    Route::delete('google/credentials/delete-key', [GoogleCredentialsController::class, 'deleteCredential']);

    /** Agenda google */
    Route::get('google/events/list', [GoogleEventsController::class, 'listEvents']);
    Route::get('google/events/show', [GoogleEventsController::class, 'showEvent']);
    Route::delete('google/events/remove', [GoogleEventsController::class, 'removeEvent']);
    Route::post('google/events/create', [GoogleEventsController::class, 'createEvent']);
    Route::put('google/events/update', [GoogleEventsController::class, 'updateEvent']);
    Route::get('google/events/watch', [GoogleEventsController::class, 'watchEvent']);
    Route::get('google/events/stop', [GoogleEventsController::class, 'stopEvent']);

    /** Cores */
    Route::get('google/colors/import', [GoogleColorsController::class, 'importColors']);
    Route::get('google/colors/list', [GoogleColorsController::class, 'listColors']);
    Route::put('google/colors/update', [GoogleColorsController::class, 'updateColors']);

    Route::post('/logout', [PassportAuthController::class, 'logout']);
    Route::get('/user', [PassportAuthController::class, 'show']);
    Route::put('/reset', [PassportAuthController::class, 'resetPassword']);
});
