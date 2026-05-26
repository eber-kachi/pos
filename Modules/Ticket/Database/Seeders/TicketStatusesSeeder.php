<?php

namespace Modules\Ticket\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TicketStatusesSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('ticket_statuses')->insert([
            [
                'name' => 'open',
                'label' => 'Abierto',
                'color' => '#EF4444',
            ],
            [
                'name' => 'assigned',
                'label' => 'Asignado',
                'color' => '#F97316',
            ],
            [
                'name' => 'in_progress',
                'label' => 'En Proceso',
                'color' => '#3B82F6',
            ],
            [
                'name' => 'pending_customer',
                'label' => 'Esperando Cliente',
                'color' => '#8B5CF6',
            ],
            [
                'name' => 'resolved',
                'label' => 'Resuelto',
                'color' => '#22C55E',
            ],
            [
                'name' => 'closed',
                'label' => 'Cerrado',
                'color' => '#6B7280',
            ],
        ]);
    }
}
