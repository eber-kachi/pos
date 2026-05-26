@extends('layouts.app')
@section('title', __('ticket::lang.my_tickets'))

@section('content')
    <section class="content-header">
        <h1>@lang('ticket::lang.my_tickets')</h1>
    </section>

    <section class="content">
        <div class="box box-solid">
            <div class="box-header">
                <div class="pull-right">
                    <a href="{{ route('ticket.create') }}" class="btn btn-primary btn-sm">
                        <i class="fa fa-plus"></i> @lang('ticket::lang.create_ticket')
                    </a>
                </div>
            </div>
            <div class="box-body">
                @if ($tickets->isEmpty())
                    <div class="callout callout-info">
                        <p>@lang('ticket::lang.no_tickets')</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>@lang('ticket::lang.code')</th>
                                    <th>@lang('ticket::lang.subject')</th>
                                    <th>@lang('ticket::lang.priority')</th>
                                    <th>@lang('ticket::lang.status')</th>
                                    <th>@lang('ticket::lang.assigned_agent')</th>
                                    <th>@lang('ticket::lang.created_at')</th>
                                    <th>@lang('ticket::lang.actions')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($tickets as $ticket)
                                    <tr>
                                        <td>{{ $ticket->code }}</td>
                                        <td>{{ $ticket->subject }}</td>
                                        <td>
                                            @php
                                                $priorityColors = [
                                                    'low' => 'bg-green',
                                                    'medium' => 'bg-yellow',
                                                    'high' => 'bg-orange',
                                                    'urgent' => 'bg-red',
                                                ];
                                            @endphp
                                            <span class="label {{ $priorityColors[$ticket->priority] ?? 'bg-gray' }}">
                                                @lang("ticket::lang.{$ticket->priority}")
                                            </span>
                                        </td>
                                        <td>
                                            <span class="label" style="background-color: {{ $ticket->status->color }}">
                                                {{ $ticket->status->label }}
                                            </span>
                                        </td>
                                        <td>{{ $ticket->assignedAgent->user_full_name ?? __('ticket::lang.not_assigned') }}</td>
                                        <td>{{ $ticket->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <a href="{{ route('ticket.show', $ticket->id) }}" class="btn btn-xs btn-info">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection
