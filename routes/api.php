<?php

use App\Http\Controllers\ZohoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers;

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

Route::get('zohocrmauth', [ZohoController::class, 'auth'])->name('zohocrmauth');
Route::get('zohocrm', [ZohoController::class, 'store'])->name('zohocrm');


