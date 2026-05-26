<?php

namespace Modules\Ticket\Entities;

use App\User;
use Illuminate\Database\Eloquent\Model;

class TicketMessage extends Model
{
    protected $table = 'ticket_messages';

    public $timestamps = false;

    protected $guarded = ['id'];

    protected $casts = [
        'is_internal_note' => 'boolean',
        'created_at' => 'datetime',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function attachments()
    {
        return $this->hasMany(TicketAttachment::class, 'ticket_message_id');
    }
}
