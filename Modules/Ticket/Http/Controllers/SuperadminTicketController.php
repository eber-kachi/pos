<?php

namespace Modules\Ticket\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Modules\Ticket\Entities\Ticket;
use Modules\Ticket\Entities\TicketAttachment;
use Modules\Ticket\Entities\TicketMessage;
use Modules\Ticket\Entities\TicketStatus;

class SuperadminTicketController extends Controller
{
    public function index()
    {
        $statuses = TicketStatus::get();
        $agents = User::role('dev#1')->get();

        $tickets = Ticket::with([
            'status',
            'business:id,name',
            'user:id,surname,first_name,last_name',
            'assignedAgent:id,surname,first_name,last_name',
            'messages' => fn ($q) => $q->where('is_internal_note', false),
        ])
            ->latest()
            ->get();

        return view('ticket::superadmin.index')
            ->with(compact('tickets', 'statuses', 'agents'));
    }

    public function show($id)
    {
        $ticket = Ticket::with([
            'status',
            'business:id,name',
            'user:id,surname,first_name,last_name,email',
            'assignedAgent:id,surname,first_name,last_name,email',
            'messages' => fn ($q) => $q->with(['user:id,surname,first_name,last_name,email', 'attachments']),
            'attachments' => fn ($q) => $q->whereNull('ticket_message_id'),
        ])
            ->findOrFail($id);

        $statuses = TicketStatus::get();
        $agents = User::role('dev#1')->get();

        return view('ticket::superadmin.show')
            ->with(compact('ticket', 'statuses', 'agents'));
    }

    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'ticket_status_id' => 'required|exists:ticket_statuses,id',
        ]);

        $ticket = Ticket::findOrFail($id);
        $ticket->ticket_status_id = $validated['ticket_status_id'];

        $newStatus = TicketStatus::find($validated['ticket_status_id']);
        if ($newStatus->name === 'closed') {
            $ticket->closed_at = now();
        }

        $ticket->save();

        return back()->with('status', ['success' => 1, 'msg' => __('ticket::lang.status_updated')]);
    }

    public function assignAgent(Request $request, $id)
    {
        $validated = $request->validate([
            'assigned_agent_id' => 'nullable|exists:users,id',
        ]);

        $ticket = Ticket::findOrFail($id);
        $ticket->assigned_agent_id = $validated['assigned_agent_id'];

        if ($ticket->wasChanged('assigned_agent_id')) {
            $assignedStatus = TicketStatus::where('name', 'assigned')->first();
            if ($assignedStatus) {
                $ticket->ticket_status_id = $assignedStatus->id;
            }
        }

        $ticket->save();

        return back()->with('status', ['success' => 1, 'msg' => __('ticket::lang.agent_assigned')]);
    }

    public function storeMessage(Request $request, $id)
    {
        $ticket = Ticket::with('status')->findOrFail($id);

        $validated = $request->validate([
            'message' => 'required|string',
            'is_internal_note' => 'boolean',
            'attachments.*' => 'nullable|file|max:10240|mimes:jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,txt,csv',
        ]);

        $message = TicketMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => $request->user()->id,
            'message' => $validated['message'],
            'is_internal_note' => $request->boolean('is_internal_note'),
            'created_at' => now(),
        ]);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store("ticket-attachments/{$ticket->id}", 'public');

                TicketAttachment::create([
                    'ticket_id' => $ticket->id,
                    'ticket_message_id' => $message->id,
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'uploaded_by' => $request->user()->id,
                ]);
            }
        }

        return back()->with('status', ['success' => 1, 'msg' => __('ticket::lang.message_sent')]);
    }
}
