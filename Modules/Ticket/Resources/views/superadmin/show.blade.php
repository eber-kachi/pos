@extends('layouts.app')
@section('title', "{$ticket->code} - {$ticket->subject}")

@section('content')
    <section class="content-header">
        <h1>{{ $ticket->code }} - {{ $ticket->subject }}</h1>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-8">
                <div class="box box-solid">
                    <div class="box-header with-border">
                        <h3 class="box-title">@lang('ticket::lang.ticket_history')</h3>
                    </div>
                    <div class="box-body">
                        <div class="direct-chat-messages" style="max-height: 500px; overflow-y: auto;">
                            <div class="direct-chat-msg">
                                <div class="direct-chat-info clearfix">
                                    <span class="direct-chat-name pull-left">{{ $ticket->user->user_full_name }}</span>
                                    <span class="direct-chat-timestamp pull-right">{{ $ticket->created_at->format('d/m/Y H:i') }}</span>
                                </div>
                                <div class="direct-chat-text">
                                    {!! nl2br(e($ticket->description)) !!}
                                </div>
                                @if ($ticket->attachments->isNotEmpty())
                                    <div class="margin-top-5">
                                        @foreach ($ticket->attachments as $attachment)
                                            <a href="{{ asset("storage/{$attachment->file_path}") }}" target="_blank" class="label label-primary">
                                                <i class="fa fa-paperclip"></i> {{ $attachment->file_name }}
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            @foreach ($ticket->messages as $message)
                                <div class="direct-chat-msg @if($message->is_internal_note) bg-warning @elseif($message->user_id !== $ticket->user_id) right @endif">
                                    <div class="direct-chat-info clearfix">
                                        <span class="direct-chat-name pull-left">
                                            {{ $message->user->user_full_name }}
                                            @if ($message->is_internal_note)
                                                <span class="label label-warning">@lang('ticket::lang.internal_note')</span>
                                            @endif
                                        </span>
                                        <span class="direct-chat-timestamp pull-right">{{ $message->created_at->format('d/m/Y H:i') }}</span>
                                    </div>
                                    <div class="direct-chat-text">
                                        {!! nl2br(e($message->message)) !!}
                                    </div>
                                    @if ($message->attachments->isNotEmpty())
                                        <div class="margin-top-5">
                                            @foreach ($message->attachments as $attachment)
                                                <a href="{{ asset("storage/{$attachment->file_path}") }}" target="_blank" class="label label-primary">
                                                    <i class="fa fa-paperclip"></i> {{ $attachment->file_name }}
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="box-footer">
                        <form action="{{ route('superadmin.ticket.store-message', $ticket->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group">
                                <textarea name="message" class="form-control" rows="3" placeholder="@lang('ticket::lang.reply')" required></textarea>
                            </div>
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="is_internal_note" value="1">
                                    @lang('ticket::lang.internal_note') - @lang('ticket::lang.internal_note_info')
                                </label>
                            </div>
                            <div class="form-group">
                                <input type="file" name="attachments[]" class="form-control" multiple accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.xls,.xlsx,.txt,.csv">
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fa fa-send"></i> @lang('ticket::lang.reply')
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="box box-solid">
                    <div class="box-header with-border">
                        <h3 class="box-title">@lang('ticket::lang.ticket')</h3>
                    </div>
                    <div class="box-body">
                        <dl class="dl-horizontal">
                            <dt>@lang('ticket::lang.code')</dt>
                            <dd>{{ $ticket->code }}</dd>
                            <dt>@lang('ticket::lang.business')</dt>
                            <dd>{{ $ticket->business->name ?? '' }}</dd>
                            <dt>@lang('ticket::lang.customer')</dt>
                            <dd>{{ $ticket->user->user_full_name ?? '' }} ({{ $ticket->user->email ?? '' }})</dd>
                            <dt>@lang('ticket::lang.status')</dt>
                            <dd>
                                <span class="label" style="background-color: {{ $ticket->status->color }}">
                                    {{ $ticket->status->label }}
                                </span>
                            </dd>
                            <dt>@lang('ticket::lang.priority')</dt>
                            <dd>@lang("ticket::lang.{$ticket->priority}")</dd>
                            <dt>@lang('ticket::lang.created_at')</dt>
                            <dd>{{ $ticket->created_at->format('d/m/Y H:i') }}</dd>
                            @if ($ticket->closed_at)
                                <dt>@lang('ticket::lang.closed_at')</dt>
                                <dd>{{ $ticket->closed_at->format('d/m/Y H:i') }}</dd>
                            @endif
                        </dl>
                    </div>
                </div>

                <div class="box box-solid">
                    <div class="box-header with-border">
                        <h3 class="box-title">@lang('ticket::lang.actions')</h3>
                    </div>
                    <div class="box-body">
                        <form action="{{ route('superadmin.ticket.update-status', $ticket->id) }}" method="POST" class="form-group">
                            @csrf
                            @method('PUT')
                            <label>@lang('ticket::lang.update_status')</label>
                            <select name="ticket_status_id" class="form-control" onchange="this.form.submit()">
                                @foreach ($statuses as $status)
                                    <option value="{{ $status->id }}" @selected($ticket->ticket_status_id === $status->id)>
                                        {{ $status->label }}
                                    </option>
                                @endforeach
                            </select>
                        </form>

                        <form action="{{ route('superadmin.ticket.assign', $ticket->id) }}" method="POST" class="form-group">
                            @csrf
                            @method('PUT')
                            <label>@lang('ticket::lang.assign_agent')</label>
                            <select name="assigned_agent_id" class="form-control" onchange="this.form.submit()">
                                <option value="">@lang('ticket::lang.not_assigned')</option>
                                @foreach ($agents as $agent)
                                    <option value="{{ $agent->id }}" @selected($ticket->assigned_agent_id === $agent->id)>
                                        {{ $agent->user_full_name }}
                                    </option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
