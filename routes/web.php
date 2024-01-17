<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LeagueController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
   return redirect('/league');
});

Route::get('/league', [LeagueController::class, 'showLeague']);
Route::get('/simulate-week', [LeagueController::class, 'simulateWeek']);
Route::get('/simulate-all-week', [LeagueController::class, 'simulateAllWeek']);
Route::get('/reset-data', [LeagueController::class, 'resetData']);
