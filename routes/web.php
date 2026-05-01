<?php

use App\Http\Controllers\ExplorerController;
use Illuminate\Support\Facades\Route;

Route::get('/', ExplorerController::class)->name('explorer.index');
