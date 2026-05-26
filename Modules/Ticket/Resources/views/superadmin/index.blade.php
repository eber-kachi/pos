@extends('layouts.app')
@section('title', __('ticket::lang.all_tickets'))

@section('content')
    <section class="content-header">
        <h1>@lang('ticket::lang.all_tickets')</h1>
    </section>

    <section class="content">
        <div class="box box-solid">
            <div class="box-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="tickets-table">
                        <thead>
                            <tr>
                                <th>@lang('ticket::lang.code')</th>
                                <th>@lang('ticket::lang.subject')</th>
                                <th>@lang('ticket::lang.business')</th>
                                <th>@lang('ticket::lang.customer')</th>
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
                                    <td>{{ $ticket->business->name ?? '' }}</td>
                                    <td>{{ $ticket->user->user_full_name ?? '' }}</td>
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
                                        <a href="{{ route('superadmin.ticket.show', $ticket->id) }}" class="btn btn-xs btn-info">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
@endsection
