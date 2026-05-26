<?php

namespace Modules\Ticket\Http\Controllers;

use Illuminate\Routing\Controller;
use Menu;

class DataController extends Controller
{
    public function modifyAdminMenu()
    {
        if (auth()->user()->can('superadmin')) {
            Menu::modify(
                'admin-sidebar-menu',
                function ($menu) {
                    $menu->url(
                        action([\Modules\Ticket\Http\Controllers\SuperadminTicketController::class, 'index']),
                        __('ticket::lang.tickets'),
                        ['icon' => 'fa fas fa-life-ring', 'active' => request()->segment(1) == 'superadmin' && request()->segment(2) == 'ticket']
                    )->order(5);
                }
            );
        }

        if (auth()->user()->can('ticket.access')) {
            Menu::modify(
                'admin-sidebar-menu',
                function ($menu) {
                    $menu->url(
                        action([\Modules\Ticket\Http\Controllers\TicketController::class, 'index']),
                        __('ticket::lang.support_tickets'),
                        ['icon' => 'fa fas fa-life-ring', 'active' => request()->segment(1) == 'ticket']
                    )->order(35);
                }
            );
        }
    }

    public function user_permissions()
    {
        return [
            [
                'value' => 'ticket.access',
                'label' => __('ticket::lang.access_tickets'),
                'default' => false,
            ],
        ];
    }
}
