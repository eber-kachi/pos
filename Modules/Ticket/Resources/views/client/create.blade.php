@extends('layouts.app')
@section('title', __('ticket::lang.new_ticket'))

@section('content')
    <section class="content-header">
        <h1>@lang('ticket::lang.new_ticket')</h1>
    </section>

    <section class="content">
        <div class="box box-solid">
            <div class="box-body">
                <form action="{{ route('ticket.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>@lang('ticket::lang.subject') <span class="text-danger">*</span></label>
                                <input type="text" name="subject" class="form-control" required maxlength="255" value="{{ old('subject') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>@lang('ticket::lang.priority') <span class="text-danger">*</span></label>
                                <select name="priority" class="form-control" required>
                                    <option value="low" @selected(old('priority') === 'low')>@lang('ticket::lang.low')</option>
                                    <option value="medium" @selected(old('priority') === 'medium')>@lang('ticket::lang.medium')</option>
                                    <option value="high" @selected(old('priority') === 'high')>@lang('ticket::lang.high')</option>
                                    <option value="urgent" @selected(old('priority') === 'urgent')>@lang('ticket::lang.urgent')</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>@lang('ticket::lang.description') <span class="text-danger">*</span></label>
                        <textarea name="description" class="form-control" rows="6" required>{{ old('description') }}</textarea>
                    </div>

                    <div class="form-group">
                        <label>@lang('ticket::lang.upload_attachments')</label>
                        <input type="file" name="attachments[]" class="form-control" multiple accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.xls,.xlsx,.txt,.csv">
                        <p class="help-block">@lang('lang_v1.max_file_size', ['size' => '10MB'])</p>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> @lang('ticket::lang.create_ticket')
                    </button>

                    <a href="{{ route('ticket.index') }}" class="btn btn-default">
                        @lang('messages.cancel')
                    </a>
                </form>
            </div>
        </div>
    </section>
@endsection
