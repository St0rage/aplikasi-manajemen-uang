<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\PiggyBankController;
use App\Models\PiggyBank;
use App\Models\User;
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

Route::middleware('auth:sanctum')->group(function () {
    // PiggyBank
    Route::get('/piggybanks', [PiggyBankController::class, 'getPiggyBanks']);
    Route::get('/piggybank/detail/{piggyBank}', [PiggyBankController::class, 'getPiggyBankDetail']);
    Route::post('/piggybank/create', [PiggyBankController::class, 'createPiggyBank']);
    Route::post('/piggybank/transaction/create/{piggyBank}', [PiggyBankController::class, 'createPiggyBankTransaction']);

    // logout
    Route::get('/logout', [AuthController::class, 'logout']);
});

// login
Route::post('/login', [AuthController::class, 'login']);