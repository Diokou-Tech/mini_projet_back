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
// recupérer tous les infos 
Route::get('/infos/{page?}/{perPage?}',[ApiXmlController::class,'getNews']);
// recuperer une information donnée à travers son titre
Route::get('/info/{titre}',[ApiXmlController::class,'getInfo']);
// modifier l'information
Route::post('/info',[ApiXmlController::class,'setInfo']);
// permet de rafraichir les infos en enregistrant dans la base de donnéess
Route::get('/refresh/infos',[ApiXmlController::class,'SaveToDB']);

