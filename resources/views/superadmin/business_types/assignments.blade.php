@extends('layouts.app')
@section('title', 'Asignar tipos de negocio')
@section('content')
@include('superadmin::layouts.nav')
<section class="content-header"><h1>Asignar tipos de negocio</h1></section>
<section class="content"><div class="box box-primary"><div class="box-body table-responsive"><table class="table table-bordered"><thead><tr><th>Negocio</th><th>Tipo asignado</th><th>Guardar</th></tr></thead><tbody>
@foreach($businesses as $business)<tr><td>{{ $business->name }}</td><td><form class="form-inline" method="POST" action="{{ route('superadmin.business-types.assign', $business->id) }}">@csrf
    <select class="form-control" name="business_type_id"><option value="">Sin tipo (mostrar todo)</option>@foreach($types as $id => $name)<option value="{{ $id }}" {{ (int) $business->business_type_id === (int) $id ? 'selected' : '' }}>{{ $name }}</option>@endforeach</select>
    <button class="btn btn-primary btn-sm">Guardar</button></form></td></tr>@endforeach
</tbody></table></div></div></section>
@endsection
