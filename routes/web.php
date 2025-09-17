<?php

use App\Http\Controllers\GachaController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\BalanceController;
use App\Http\Controllers\AdminExportController;
use App\Http\Controllers\NameController;
use App\Http\Controllers\RankingController;

Route::get('/',        [GachaController::class, 'home'])->name('gacha.home');
Route::post('/draw',   [GachaController::class, 'draw'])->name('gacha.draw');
Route::get('/history', [GachaController::class, 'history'])->name('gacha.history');
Route::get('/catalog', [GachaController::class, 'catalog'])->name('gacha.catalog');
Route::prefix('admin')->group(function () {
    Route::get('/items',        [ItemController::class, 'index'])->name('items.index');
    Route::get('/items/{item}/edit', [ItemController::class, 'edit'])->name('items.edit');
    Route::put('/items/{item}', [ItemController::class, 'update'])->name('items.update');
    Route::post('/items/{item}/image', [ItemController::class, 'upload'])->name('items.upload');
});
// 新規アイテム
Route::get('/admin/items/create', [ItemController::class,'create'])->name('items.create');
Route::post('/admin/items', [ItemController::class,'store'])->name('items.store');

// 簡易バランス調整（レア度倍率）
Route::get('/admin/balance',  [BalanceController::class,'edit'])->name('balance.edit');
Route::post('/admin/balance', [BalanceController::class,'update'])->name('balance.update');

// CSVエクスポート（履歴）
Route::get('/admin/history/export', [AdminExportController::class,'historyCsv'])->name('admin.export.history');

Route::get('/name', [NameController::class, 'edit'])->name('name.edit');
Route::post('/name', [NameController::class, 'update'])->name('name.update');


Route::post('/set-username', function(Request $request) {
    $request->session()->put('username', $request->input('username'));
    return back();
})->name('set-username');

Route::get('/ranking', [\App\Http\Controllers\RankingController::class, 'index'])
    ->name('ranking.index');

    Route::get('/ranking/csv', [\App\Http\Controllers\RankingController::class, 'csv'])
    ->name('ranking.csv');