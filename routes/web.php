<?php

use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KnowledgeArticleController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'loginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::get('/register', [AuthController::class, 'registerForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::get('/knowledge', [KnowledgeArticleController::class, 'index'])->name('knowledge.index');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');
    Route::get('/tickets/create', [TicketController::class, 'create'])->name('tickets.create');
    Route::post('/tickets', [TicketController::class, 'store'])->name('tickets.store');
    Route::get('/tickets/{ticket}', [TicketController::class, 'show'])->name('tickets.show');
    Route::get('/tickets/{ticket}/edit', [TicketController::class, 'edit'])->name('tickets.edit');
    Route::patch('/tickets/{ticket}', [TicketController::class, 'update'])->name('tickets.update');
    Route::post('/tickets/{ticket}/replies', [TicketController::class, 'reply'])->name('tickets.reply');
    Route::get('/attachments/{attachment}', [TicketController::class, 'download'])->name('attachments.download');

    Route::get('/knowledge/create', [KnowledgeArticleController::class, 'create'])->name('knowledge.create');
    Route::post('/knowledge', [KnowledgeArticleController::class, 'store'])->name('knowledge.store');
    Route::get('/knowledge/{article}/edit', [KnowledgeArticleController::class, 'edit'])->name('knowledge.edit');
    Route::patch('/knowledge/{article}', [KnowledgeArticleController::class, 'update'])->name('knowledge.update');

    Route::get('/analytics', AnalyticsController::class)->name('analytics');
});

Route::get('/knowledge/{article}', [KnowledgeArticleController::class, 'show'])->name('knowledge.show');
