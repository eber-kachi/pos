@extends('layouts.app')
@section('title', 'Tipos de negocio')
@section('content')
@include('superadmin::layouts.nav')
<section class="content-header"><h1>Tipos de negocio</h1></section>
<section class="content">
    <div class="box box-primary"><div class="box-header with-border">
        <a class="btn btn-primary" href="{{ action([App\Http\Controllers\Superadmin\BusinessTypeController::class, 'create']) }}"><i class="fa fa-plus"></i> Crear tipo</a>
        <a class="btn btn-default" href="{{ route('superadmin.business-types.assignments') }}">Asignar a negocios</a>
    </div><div class="box-body table-responsive"><table class="table table-bordered"><thead><tr><th>Nombre</th><th>Negocios asignados</th><th></th></tr></thead><tbody>
    @forelse($types as $type)<tr><td>{{ $type->name }}</td><td>{{ $type->businesses_count }}</td><td><a class="btn btn-xs btn-primary" href="{{ action([App\Http\Controllers\Superadmin\BusinessTypeController::class, 'edit'], $type->id) }}">Editar</a></td></tr>
    @empty<tr><td colspan="3">Aún no hay tipos de negocio.</td></tr>@endforelse
    </tbody></table></div></div>
</section>
@endsection
