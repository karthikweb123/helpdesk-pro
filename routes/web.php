<?php

use App\Http\Controllers\Admin\AgentController as AdminAgentController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\TicketController as AdminTicketController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KnowledgeArticleController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'loginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('login.store');
    Route::get('/register', [AuthController::class, 'registerForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1')->name('register.store');
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::get('/knowledge', [KnowledgeArticleController::class, 'index'])->name('knowledge.index');

Route::middleware('auth')->group(function () {
    Route::get('/account/password', [AdminProfileController::class, 'passwordForm'])->name('account.password.edit');
    Route::put('/account/password', [AdminProfileController::class, 'updatePassword'])->name('account.password.update');
});

Route::middleware(['auth', 'password.changed'])->group(function () {
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

    Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
        Route::get('/', AdminDashboardController::class)->name('dashboard');

        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [AdminUserController::class, 'create'])->name('users.create');
        Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
        Route::patch('/users/{user}/active', [AdminUserController::class, 'toggleActive'])->name('users.active');

        Route::get('/tickets', [AdminTicketController::class, 'index'])->name('tickets.index');
        Route::get('/agents', [AdminAgentController::class, 'index'])->name('agents.index');

        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');

        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit.index');
        Route::get('/profile', [AdminProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [AdminProfileController::class, 'update'])->name('profile.update');
    });
});

Route::get('/knowledge/{article}', [KnowledgeArticleController::class, 'show'])->name('knowledge.show');
