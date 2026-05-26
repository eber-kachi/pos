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
                                <div class="direct-chat-msg @if($message->user_id !== $ticket->user_id) right @endif">
                                    <div class="direct-chat-info clearfix">
                                        <span class="direct-chat-name pull-left">{{ $message->user->user_full_name }}</span>
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

                    @if ($ticket->status->name !== 'closed')
                        <div class="box-footer">
                            <form action="{{ route('ticket.store-message', $ticket->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="form-group">
                                    <textarea name="message" class="form-control" rows="3" placeholder="@lang('ticket::lang.reply')" required></textarea>
                                </div>
                                <div class="form-group">
                                    <input type="file" name="attachments[]" class="form-control" multiple accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.xls,.xlsx,.txt,.csv">
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="fa fa-send"></i> @lang('ticket::lang.reply')
                                </button>
                            </form>
                        </div>
                    @else
                        <div class="box-footer">
                            <div class="callout callout-default">
                                <p>@lang('ticket::lang.ticket_closed_no_replies')</p>
                            </div>
                        </div>
                    @endif
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
                            <dt>@lang('ticket::lang.status')</dt>
                            <dd>
                                <span class="label" style="background-color: {{ $ticket->status->color }}">
                                    {{ $ticket->status->label }}
                                </span>
                            </dd>
                            <dt>@lang('ticket::lang.priority')</dt>
                            <dd>@lang("ticket::lang.{$ticket->priority}")</dd>
                            <dt>@lang('ticket::lang.assigned_agent')</dt>
                            <dd>{{ $ticket->assignedAgent->user_full_name ?? __('ticket::lang.not_assigned') }}</dd>
                            <dt>@lang('ticket::lang.created_at')</dt>
                            <dd>{{ $ticket->created_at->format('d/m/Y H:i') }}</dd>
                            @if ($ticket->closed_at)
                                <dt>@lang('ticket::lang.closed_at')</dt>
                                <dd>{{ $ticket->closed_at->format('d/m/Y H:i') }}</dd>
                            @endif
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
