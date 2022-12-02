<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\BalanceController;
use App\Http\Controllers\API\PiggyBankController;
use App\Http\Controllers\API\UserController;
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
    Route::get('/piggybank/{piggyBank}/detail', [PiggyBankController::class, 'getPiggyBankDetail']);
    Route::post('/piggybank/create', [PiggyBankController::class, 'createPiggyBank']);
    Route::put('/piggybank/{piggyBank}/update', [PiggyBankController::class, 'updatePiggyBank']);
    Route::delete('/piggybank/{piggyBank}/delete/', [PiggyBankController::class, 'deletePiggyBank']);
    Route::get('/piggybank/{piggyBank}/transactions', [PiggyBankController::class, 'getPiggyBankTransactions']);
    Route::post('/piggybank/{piggyBank}/transaction/deposit', [PiggyBankController::class, 'createPiggyBankTransaction']);
    Route::post('/piggybank/{piggyBank}/transaction/withdraw', [PiggyBankController::class, 'substractPiggyBankTransaction']);
    Route::delete('/piggybank/transaction/{piggyBankTransaction}/delete', [PiggyBankController::class, 'deletePiggyBankTransaction']);

    // Whislist
    Route::get('/whislists', [WhislistController::class, 'getWhislists']);
    Route::get('/whislist/{whislist}/detail', [WhislistController::class, 'getWhislistDetail']);
    Route::post('/whislist/create', [WhislistController::class, 'createWhislist']);
    Route::put('/whislist/{whislist}/update', [WhislistController::class, 'updateWhislist']);
    Route::delete('/whislist/{whislist}/delete', [WhislistController::class, 'deleteWhislist']);
    Route::get('/whislist/{whislist}/transactions', [WhislistController::class, 'getWhislistTransactions']);
    Route::post('/whislist/{whislist}/transaction/deposit', [WhislistController::class, 'depositWhislistTransaction']);
    Route::post('/whislist/{whislist}/transaction/withdraw', [WhislistController::class, 'withdrawWhislistTransaction']);
    Route::delete('/whislist/transaction/{whislistTransaction}/delete', [WhislistController::class, 'deleteWhislistTransaction']);

    // Balance
    Route::get('/balance', [BalanceController::class, 'getBalance']);

    // Register
    Route::post('/register', [AuthController::class, 'register']);

    // Reset Password
    Route::put('/resetpassword', [AuthController::class, 'resetPassword']);

    // User
    Route::get('/user', [UserController::class, 'getUser']);
    Route::put('/user/changepassword', [UserController::class, 'changePassword']);

    // logout
    Route::get('/logout', [AuthController::class, 'logout']);
});

// login
Route::post('/login', [AuthController::class, 'login']);