<?php

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
Route::post('/cadastrarDevice', 'DeviceController@cadastrar');
Route::post('/teste', 'DeviceController@teste');

Route::middleware(['guard.jwt:device', 'jwt.auth'])->group(function () {
    Route::post('/atualizarLocalizacao', 'LocalizacaoController@atualizar');
    Route::post('/teste', 'LocalizacaoController@teste');
    Route::post('/atualizarStatus', 'DeviceController@atualizarStatus');
});
