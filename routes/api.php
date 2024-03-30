<?php

use App\Http\Controllers\APIs\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::post('/register', [AuthController::class, 'register']);
// Route::post('/login', [AuthController::class, 'login']);

Route::controller(AuthController::class)->group(function(){
    Route::post('/register', 'register');
    Route::post('/login', 'login');
    Route::get('/logout', 'logout');
    Route::get('/refresh', 'refresh');

});
Route::get('email/verify/{id}', [AuthController::class, 'verify'])->name('verification.verify');
Route::get('email/verify', [AuthController::class, 'notice'])->name('verification.notice');
Route::get('email/resend', [AuthController::class, 'resend'])->name('verification.resend');
