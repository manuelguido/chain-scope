<?php

use App\Http\Controllers\AddressController;
use App\Http\Controllers\BlockController;
use App\Http\Controllers\ExplorerController;
use App\Http\Controllers\LiveController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::get('/', ExplorerController::class)->name('explorer.index');

Route::get('/search', SearchController::class)->name('search');

Route::get('/tx/{hash}', TransactionController::class)
    ->where('hash', '0x[0-9a-fA-F]{4,64}')
    ->name('tx.show');

Route::get('/block/{height}', BlockController::class)
    ->where('height', '[0-9]+')
    ->name('block.show');

Route::get('/address/{address}', AddressController::class)
    ->where('address', '0x[0-9a-fA-F]{40}')
    ->name('address.show');

// Lightweight JSON endpoints for live updates
Route::get('/api/live/tip', [LiveController::class, 'tip'])->name('api.live.tip');
Route::get('/api/live/blocks', [LiveController::class, 'blocks'])->name('api.live.blocks');
Route::get('/api/live/transactions', [LiveController::class, 'transactions'])->name('api.live.transactions');
