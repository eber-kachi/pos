<?php

namespace Modules\Ticket\Http\Controllers;

use App\User;
use App\Utils\ModuleUtil;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Ticket\Entities\Ticket;
use Modules\Ticket\Entities\TicketAttachment;
use Modules\Ticket\Entities\TicketMessage;
use Modules\Ticket\Entities\TicketStatus;
use Modules\Ticket\Mail\TicketCreatedMail;

class TicketController extends Controller
{
    public function index()
    {
        $business_id = request()->session()->get('user.business_id');

        $statuses = TicketStatus::get();

        $tickets = Ticket::with([
            'status',
            'assignedAgent',
            'messages' => fn ($q) => $q->where('is_internal_note', false),
        ])
            ->where('business_id', $business_id)
            ->latest()
            ->get();

        return view('ticket::client.index')
            ->with(compact('tickets', 'statuses'));
    }

    public function create()
    {
        $statuses = TicketStatus::get();

        return view('ticket::client.create')
            ->with(compact('statuses'));
    }

    public function store(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');

        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|in:low,medium,high,urgent',
            'attachments.*' => 'nullable|file|max:10240|mimes:jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,txt,csv',
        ]);

        $openStatus = TicketStatus::where('name', 'open')->firstOrFail();

        $code = $this->generateCode();

        $ticket = Ticket::create([
            'code' => $code,
            'business_id' => $business_id,
            'user_id' => $request->user()->id,
            'ticket_status_id' => $openStatus->id,
            'priority' => $validated['priority'],
            'subject' => $validated['subject'],
            'description' => $validated['description'],
        ]);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('ticket-attachments/' . $ticket->id, 'public');

                TicketAttachment::create([
                    'ticket_id' => $ticket->id,
                    'ticket_message_id' => null,
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'uploaded_by' => $request->user()->id,
                ]);
            }
        }

        Mail::to('eber.kachi96@gmail.com')
            ->send(new TicketCreatedMail($ticket));

        return redirect()
            ->route('ticket.show', $ticket->id)
            ->with('status', ['success' => 1, 'msg' => __('ticket::lang.ticket_created')]);
    }

    public function show($id)
    {
        $business_id = request()->session()->get('user.business_id');

        $ticket = Ticket::with([
            'status',
            'assignedAgent',
            'user',
            'messages' => fn ($q) => $q->where('is_internal_note', false)
                ->with(['user', 'attachments']),
            'attachments' => fn ($q) => $q->whereNull('ticket_message_id'),
        ])
            ->where('business_id', $business_id)
            ->findOrFail($id);

        $statuses = TicketStatus::get();

        return view('ticket::client.show')
            ->with(compact('ticket', 'statuses'));
    }

    public function storeMessage(Request $request, $id)
    {
        $business_id = $request->session()->get('user.business_id');

        $ticket = Ticket::with('status')
            ->where('business_id', $business_id)
            ->findOrFail($id);

        if ($ticket->status->name === 'closed') {
            return back()->with('status', [
                'success' => 0,
                'msg' => __('ticket::lang.ticket_closed_no_replies'),
            ]);
        }

        $validated = $request->validate([
            'message' => 'required|string',
            'attachments.*' => 'nullable|file|max:10240|mimes:jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,txt,csv',
        ]);

        $message = TicketMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => $request->user()->id,
            'message' => $validated['message'],
            'is_internal_note' => false,
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

    private function generateCode(): string
    {
        $year = now()->format('Y');
        $last = Ticket::whereYear('created_at', $year)
            ->lockForUpdate()
            ->latest('id')
            ->first();

        $next = $last ? ((int) Str::afterLast($last->code, '-')) + 1 : 1;

        return "TCK-{$year}-" . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }
}
