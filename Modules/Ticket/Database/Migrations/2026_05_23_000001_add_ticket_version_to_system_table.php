<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddTicketVersionToSystemTable extends Migration
{
    public function up()
    {
        DB::table('system')->insert(
            ['key' => 'ticket_version', 'value' => config('ticket.module_version')]
        );
    }

    public function down()
    {
        DB::table('system')->where('key', 'ticket_version')->delete();
    }
}
