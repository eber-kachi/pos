@extends('layouts.app')
@section('title', 'Importar stock inicial')

@section('content')
<br/>
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">Importar stock inicial</h1>
</section>

<!-- Main content -->
<section class="content">

@if (session('notification') || !empty($notification))
    <div class="row">
        <div class="col-sm-12">
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                @if(!empty($notification['msg']))
                    {{$notification['msg']}}
                @elseif(session('notification.msg'))
                    {{ session('notification.msg') }}
                @endif
              </div>
          </div>
      </div>
@endif

    <div class="row">
        <div class="col-sm-12">
            @component('components.widget', ['class' => 'box-primary', 'title' => 'Paso 1: descargue la plantilla con sus productos'])
                <form action="{{ url('import-opening-stock/plantilla') }}" method="get" class="form-inline">
                    <div class="form-group">
                        <label for="location_id">Ubicación:</label>
                        <select name="location_id" id="location_id" class="form-control" required>
                            @if($locations->count() > 1)
                                <option value="">Seleccione una ubicación</option>
                            @endif
                            @foreach($locations as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="tw-dw-btn tw-dw-btn-success tw-text-white">
                        <i class="fa fa-download"></i> Descargar plantilla
                    </button>
                </form>
                <p class="text-muted" style="margin-top:10px">
                    El archivo trae todos los productos que administran stock, con su SKU y nombre.
                    Si un producto ya tiene stock inicial en esa ubicación, vendrá con su cantidad y costo actuales.
                </p>
            @endcomponent
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12">
            @component('components.widget', ['class' => 'box-primary', 'title' => 'Paso 2: suba el archivo lleno'])
                {!! Form::open(['url' => action([\App\Http\Controllers\ImportOpeningStockController::class, 'store']), 'method' => 'post', 'enctype' => 'multipart/form-data' ]) !!}
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="products_csv">Archivo de Excel:</label>
                                {!! Form::file('products_csv', ['id' => 'products_csv', 'accept' => '.xlsx,.xls,.csv', 'required' => 'required']); !!}
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <br>
                            <button type="submit" class="tw-dw-btn tw-dw-btn-primary tw-text-white">Importar</button>
                        </div>
                    </div>
                {!! Form::close() !!}
            @endcomponent
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12">
            @component('components.widget', ['class' => 'box-primary', 'title' => 'Instrucciones'])
                <ol>
                    <li>Escriba la <b>Cantidad</b> solo en los productos que quiera cargar. Las filas sin cantidad se omiten.</li>
                    <li>Revise el <b>Costo unitario (antes de impuesto)</b>; viene con el precio de compra actual del producto.</li>
                    <li>No cambie el <b>SKU</b>, es lo que identifica al producto. La columna <b>Producto</b> es solo informativa.</li>
                    <li>Lote y fecha de vencimiento son opcionales. La fecha debe estar en el formato <b>{{ $date_format }}</b> (ejemplo: <b>{{ @format_date('today') }}</b>) o ser una fecha de Excel.</li>
                </ol>
                <div class="alert alert-warning" style="margin-bottom:0">
                    <b>Si vuelve a importar, el stock inicial se reemplaza, no se suma.</b>
                    El sistema ajusta solo la diferencia en las existencias. No se permite dejar un stock inicial menor
                    a lo que ya se haya vendido o ajustado de ese producto.
                </div>
                <br>
                <p><b>Qué se revisa antes de guardar</b> (si algo falla se muestran todos los errores con su número de fila y no se guarda nada):</p>
                <ul>
                    <li>El SKU existe y el producto tiene activado "Administrar stock".</li>
                    <li>La ubicación existe y usted tiene permiso para usarla.</li>
                    <li>La cantidad es un número mayor que 0, y el costo es un número igual o mayor que 0.</li>
                    <li>El SKU no está repetido para la misma ubicación en el archivo.</li>
                    <li>La fecha de vencimiento es válida.</li>
                </ul>
            @endcomponent
        </div>
    </div>
</section>
<!-- /.content -->

@endsection
