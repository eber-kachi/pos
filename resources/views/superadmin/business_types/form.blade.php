@extends('layouts.app')
@section('title', $type->exists ? 'Editar tipo de negocio' : 'Crear tipo de negocio')
@section('content')
@include('superadmin::layouts.nav')
<section class="content-header"><h1>{{ $type->exists ? 'Editar tipo de negocio' : 'Crear tipo de negocio' }}</h1></section>
<section class="content"><div class="box box-primary"><form method="POST" action="{{ $type->exists ? action([App\Http\Controllers\Superadmin\BusinessTypeController::class, 'update'], $type->id) : action([App\Http\Controllers\Superadmin\BusinessTypeController::class, 'store']) }}">
    @csrf @if($type->exists) @method('PUT') @endif
    <div class="box-body"><div class="form-group"><label for="name">Nombre del tipo</label><input class="form-control" id="name" name="name" value="{{ old('name', $type->name) }}" required maxlength="191"></div>
    <h4>Funciones y columnas</h4>
    <p class="text-muted">Desactiva una función para ocultarla en el formulario de productos. Las columnas se pueden usar después para filtrar reportes y tablas.</p>
    @foreach($features as $key => $feature)
        @php($settings = $featureSettings[$key] ?? [])
        <div class="panel panel-default"><div class="panel-heading"><strong>{{ $feature['label'] }}</strong></div><div class="panel-body">
            <div class="checkbox"><label><input type="checkbox" name="features[{{ $key }}]" value="1" {{ old('features.'.$key, $settings['is_enabled'] ?? true) ? 'checked' : '' }}> Mostrar esta función</label></div>
            <div class="form-group"><label>Columnas ocultas (claves separadas por coma)</label><input class="form-control" name="hidden_columns[{{ $key }}]" value="{{ old('hidden_columns.'.$key, implode(',', $settings['hidden_columns'] ?? $feature['columns'])) }}"></div>
            <div class="checkbox"><label><input type="checkbox" name="hide_in_reports[{{ $key }}]" value="1" {{ old('hide_in_reports.'.$key, $settings['hide_in_reports'] ?? false) ? 'checked' : '' }}> Ocultar estas columnas en reportes</label></div>
            <div class="checkbox"><label><input type="checkbox" name="hide_in_datatables[{{ $key }}]" value="1" {{ old('hide_in_datatables.'.$key, $settings['hide_in_datatables'] ?? false) ? 'checked' : '' }}> Ocultar estas columnas en DataTables</label></div>
        </div></div>
    @endforeach
    </div><div class="box-footer"><button class="btn btn-primary" type="submit">Guardar</button><a class="btn btn-default" href="{{ action([App\Http\Controllers\Superadmin\BusinessTypeController::class, 'index']) }}">Cancelar</a></div>
</form></div></section>
@endsection
