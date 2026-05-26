<?php

use Illuminate\Support\Facades\Route;
use Modules\Ticket\Http\Controllers\TicketController;
use Modules\Ticket\Http\Controllers\SuperadminTicketController;

Route::middleware('web', 'auth', 'language', 'AdminSidebarMenu')
    ->prefix('ticket')
    ->group(function () {
        Route::get('/', [TicketController::class, 'index'])->name('ticket.index');
        Route::get('/create', [TicketController::class, 'create'])->name('ticket.create');
        Route::post('/', [TicketController::class, 'store'])->name('ticket.store');
        Route::get('/{id}', [TicketController::class, 'show'])->name('ticket.show');
        Route::post('/{id}/message', [TicketController::class, 'storeMessage'])->name('ticket.store-message');
    });

Route::middleware('web', 'auth', 'language', 'AdminSidebarMenu', 'superadmin')
    ->prefix('superadmin/ticket')
    ->group(function () {
        Route::get('/', [SuperadminTicketController::class, 'index'])->name('superadmin.ticket.index');
        Route::get('/{id}', [SuperadminTicketController::class, 'show'])->name('superadmin.ticket.show');
        Route::put('/{id}/status', [SuperadminTicketController::class, 'updateStatus'])->name('superadmin.ticket.update-status');
        Route::put('/{id}/assign', [SuperadminTicketController::class, 'assignAgent'])->name('superadmin.ticket.assign');
        Route::post('/{id}/message', [SuperadminTicketController::class, 'storeMessage'])->name('superadmin.ticket.store-message');
    });
