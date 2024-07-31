<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PetController;

Route::get('/', [PetController::class, 'index'])->name('pets.index');

Route::post('/pet/search', [PetController::class, 'show'])->name('pets.search');
Route::post('/pet', [PetController::class, 'store'])->name('pets.store');
Route::post('/pet/{id}', [PetController::class, 'update'])->name('pets.update');
Route::delete('/pet/{id}', [PetController::class, 'destroy'])->name('pets.destroy');
