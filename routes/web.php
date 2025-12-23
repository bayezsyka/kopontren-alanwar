<?php

use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/login', function () {
    return view('login');
})->name('login');

Route::get('/offline', function () {
    return view('offline');
})->name('offline');

// Protected routes (akan dihandle oleh JavaScript untuk auth check)
Route::get('/', function () {
    return view('pos');
})->name('home');

Route::get('/pos', function () {
    return view('pos');
})->name('pos');

Route::get('/restock', function () {
    return view('restock');
})->name('restock');

Route::get('/items', function () {
    return view('items');
})->name('items');

Route::get('/stock', function () {
    return view('stock');
})->name('stock');

// Owner only
Route::get('/owner/dashboard', function () {
    return view('owner.dashboard');
})->name('owner.dashboard');

Route::get('/owner/reports', function () {
    return view('owner.reports');
})->name('owner.reports');

Route::get('/owner/reports/{id}', function ($id) {
    return view('owner.report-detail', ['reportId' => $id]);
})->name('owner.reports.detail');

// Logout (just redirect to login, actual logout handled by JS)
Route::get('/logout', function () {
    return view('logout');
})->name('logout');
