<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LeadController;

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
    return redirect('auth/redirect');
});


Route::get('/auth/redirect', [AuthController::class, 'redirectToAmo']);
Route::get('/auth/callback', [AuthController::class, 'handleCallback']);
Route::get('/leads', [LeadController::class, 'index'])->name('leads.index');
