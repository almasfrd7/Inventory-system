<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('dashboard'));
Route::get('/inventory', fn () => view('inventory'));
Route::get('/login', fn () => view('login'));
