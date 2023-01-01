<?php

use App\Http\Controllers\Auth\SanctumController;
use App\Http\Controllers\ConsultingController;
use App\Http\Controllers\ExpertController;
use App\Http\Controllers\HomeController;
use App\Models\Consultings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\UserController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/login', [SanctumController::class, 'login'])->name('home.login');
Route::post('/register', [SanctumController::class, 'register'])->name('home.register');


Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::get('/home', [HomeController::class, 'home']);
    Route::get('/logout', [SanctumController::class, 'logout']);
    Route::get('/consultings/search/{name}', [ConsultingController::class, 'consultingsSearch']);
    Route::get('/consultings/{id}', [ConsultingController::class, 'consultingExperts']);
    Route::get('/reservation/available/{id}', [ReservationController::class, 'getAvailableAppointments']);
    Route::get('/reservation/reserved', [ReservationController::class, 'getReservedAppointments']);
    Route::post('/reservation/reserve/{id}', [ReservationController::class, 'makeReservation']);
    Route::get('/consultings', [ConsultingController::class, 'getConsultings'])->name('home.getConsultings');
    Route::post('/rate/{expert_id}',[UserController::class,'rateExpert']);
});
Route::get('/experts/search/{name}', [ExpertController::class, 'expertsSearch']);
Route::get('/expert/{id}', [ExpertController::class, 'expertDetails']);

