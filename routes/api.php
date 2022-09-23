<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\PiggyBankController;
use App\Http\Controllers\API\WhislistController;
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
    Route::put('/piggybank/update/{piggyBank}', [PiggyBankController::class, 'updatePiggyBank']);
    Route::delete('/piggybank/delete/{piggyBank}', [PiggyBankController::class, 'deletePiggyBank']);
    Route::post('/piggybank/transaction/create/{piggyBank}', [PiggyBankController::class, 'createPiggyBankTransaction']);
    Route::post('/piggybank/transaction/substract/{piggyBank}', [PiggyBankController::class, 'substractPiggyBankTransaction']);
    Route::delete('/piggybank/transaction/delete/{piggyBankTransaction}', [PiggyBankController::class, 'deletePiggyBankTransaction']);

    // Whislist
    Route::get('/whislists', [WhislistController::class, 'getWhislists']);
    Route::get('/whislist/detail/{whislist}', [WhislistController::class, 'getWhislistDetail']);
    Route::post('/whislist/create', [WhislistController::class, 'createWhislist']);
    Route::put('/whislist/update/{whislist}', [WhislistController::class, 'updateWhislist']);

    Route::post('/whislist/transaction/create/{whislist}', [WhislistController::class, 'createWhislistTransaction']);
    Route::post('/whislist/transaction/substract/{whislist}', [WhislistController::class, 'substractWhislistTransaction']);

    // logout
    Route::get('/logout', [AuthController::class, 'logout']);
});

// login
Route::post('/login', [AuthController::class, 'login']);