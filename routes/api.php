<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiXmlController;

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

Route::get('/infos/{page?}/{perPage?}',[ApiXmlController::class,'getNews']);
Route::get('/info/{titre}',[ApiXmlController::class,'getInfo']);
Route::post('/info',[ApiXmlController::class,'setInfo']);
Route::get('/saveDB',[ApiXmlController::class,'SaveToDB']);

