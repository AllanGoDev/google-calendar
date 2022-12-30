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
Route::post('/logout', [PassportAuthController::class, 'logout']);

/** Login Oauth */
Route::get('google/oauth', [GoogleAccountController::class, 'getAuth']);
Route::post('google/auth/login', [GoogleAccountController::class, 'getAuth']);
Route::get('google/login/url', [GoogleAccountController::class, 'getAuthUrl']);

Route::group(['middleware' => 'auth:api'], function () {
    Route::get('google/calendar', [GoogleAccountController::class, 'getDrive']);

    /** Credenciais */
    Route::post('google/register-key', [GoogleCredentialsController::class, 'createCredential']);
    Route::get('google/list-keys', [GoogleCredentialsController::class, 'listCredentials']);
    Route::put('google/update-key/{id}', [GoogleCredentialsController::class, 'updateCredential']);
    Route::delete('google/delete-key/{id}', [GoogleCredentialsController::class, 'deleteCredential']);

    /** Agenda google */
    Route::get('google/events/list', [GoogleEventsController::class, 'listEvents']);
    Route::get('google/events/show', [GoogleEventsController::class, 'showEvent']);
    Route::delete('google/events/remove', [GoogleEventsController::class, 'removeEvent']);
    Route::post('google/events/create', [GoogleEventsController::class, 'createEvent']);
    Route::put('google/events/update', [GoogleEventsController::class, 'updateEvent']);

    /** Cores */
    Route::get('google/colors/import', [GoogleColorsController::class, 'importColors']);
    Route::get('google/colors/list', [GoogleColorsController::class, 'listColors']);
    Route::put('google/colors/update/{id}', [GoogleColorsController::class, 'updateColors']);
});
