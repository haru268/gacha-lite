<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\GachaApiController;

Route::post('/gacha', [GachaApiController::class, 'draw']);
Route::get('/history', [GachaApiController::class, 'history']);
Route::get('/ranking', [GachaApiController::class, 'ranking']);