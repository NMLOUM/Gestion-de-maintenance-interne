<?php
// routes/web.php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\EvaluationController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Symfony\Component\HttpKernel\Profiler\Profile;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Historique complet (Responsable IT et Direction uniquement)
    Route::get('/history', [DashboardController::class, 'history'])
        ->middleware('role:responsable_it,direction')
        ->name('history.index');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Tickets
    Route::resource('tickets', TicketController::class)->middleware('ticket.access');

    // Actions spécifiques aux tickets avec contrôle d'accès
    Route::middleware('ticket.access')->group(function () {
        Route::post('/tickets/{ticket}/status', [TicketController::class, 'updateStatus'])->name('tickets.status');
        Route::post('/tickets/{ticket}/comment', [TicketController::class, 'addComment'])->name('tickets.comment');
        Route::post('/tickets/{ticket}/attachment', [TicketController::class, 'uploadAttachment'])->name('tickets.attachment');
    });

    // Actions réservées aux responsables (assignation)
    Route::middleware('role:responsable_it,direction')->group(function () {
        Route::post('/tickets/{ticket}/assign', [TicketController::class, 'assign'])->name('tickets.assign');
    });

    // Pièces jointes
    Route::delete('/attachments/{attachment}', [TicketController::class, 'deleteAttachment'])->name('attachments.destroy');

    // Évaluations
    Route::post('/tickets/{ticket}/evaluate', [EvaluationController::class, 'store'])->name('evaluations.store');
    Route::delete('/evaluations/{evaluation}', [EvaluationController::class, 'destroy'])
        ->middleware('role:direction,responsable_it')
        ->name('evaluations.destroy');

    // Rapports (pour responsable_it, direction et techniciens)
    Route::middleware('role:responsable_it,direction,technicien')->group(function () {
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::post('/reports/generate', [ReportController::class, 'generate'])->name('reports.generate');
    });

    // Gestion des utilisateurs (direction et responsable_it)
    Route::middleware('role:direction,responsable_it')->group(function () {
        Route::resource('users', UserController::class);
        Route::post('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
    });

    // Notifications
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/unread', [NotificationController::class, 'unread'])->name('unread');
        Route::get('/count', [NotificationController::class, 'count'])->name('count');
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('mark-read');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
    });
});

require __DIR__.'/auth.php';