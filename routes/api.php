<?php

use App\Http\Controllers\HomeController;
use App\Models\Consultings;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/login' , [HomeController::class , 'login'])->name('home.login');
Route::post('/register' , [HomeController::class , 'register'])->name('home.register');


Route::group(['middleware' => 'auth:sanctum'] , function(){
    Route::get('/home' , [HomeController::class , 'home'])->name('home.home');
    Route::get('/logout' , [HomeController::class , 'logout'])->name('home.logout');
});

Route::get('/consultings' , [HomeController::class , 'getConsultings'])->name('home.getConsultings');



