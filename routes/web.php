<?php

use Illuminate\Support\Facades\Route;
use PhpParser\Builder\Function_;
use PhpParser\Node\Expr\FuncCall;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function() {
    return response()->json([
        'code' => 404,
        'status' => 'not found'
    ], 404);
});

Route::fallback(function() {
    return response()->json([
        'code' => 404,
        'status' => 'not found'
    ], 404);
});

