<?php

namespace Modules\Ticket\Database\Seeders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class TicketDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Model::unguard();
        $this->call(TicketStatusesSeeder::class);
    }
}
