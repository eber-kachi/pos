<?php

namespace Modules\Ticket\Entities;

use Illuminate\Database\Eloquent\Model;

class TicketStatus extends Model
{
    protected $table = 'ticket_statuses';

    public $timestamps = false;

    protected $guarded = ['id'];

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'ticket_status_id');
    }
}
