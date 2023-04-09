<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ConcertController;
use App\Http\Controllers\ConcertOrdersController;

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

Route::get('/concert/{id}', [ConcertController::class, 'show']);
Route::post('/concert/{id}/orders', [ConcertOrdersController::class, 'store']);

Routes::get("/home", ConcertOrdersController::class, "home")

Route::get('/', function () {
    return view("welcome");
});
