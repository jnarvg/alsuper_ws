@extends('layouts.admin')
@section('title')
Webservice - Sucursales
@endsection
@section('filter')
  <a href="#" data-toggle="modal" data-target="#modal-uso_propiedad"><button class="mb-0 d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i class="fas fa-plus"></i></button></a>
@endsection
@section('content')
<div class="content mt-3">
  <div class="card">
    <div class="card-body">
      {{ Form::open(array('action'=>array('WebServiceController@ws_empleados'),'method'=>'get')) }}
        <div class="row">
          <div class="col-md-3">
            <div class="form-group">
              <label for="id_empresa">ID Empresa</label>
              <input type="text" name="id_empresa" id="id_empresa" value="Bhermanos" class="form-control">
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
              <label for="id_plaza">ID Plaza</label>
              <input type="text" name="id_plaza" id="id_plaza" value="12" class="form-control">
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
              <button class="btn btn-primary" type="submit" style="margin-top: 2rem;">Enviar</button>
            </div>
          </div>
        </div>
      {{ Form::close()}}
    </div>
  </div>
  <div class="card">
    <div class="card-body">
      <h3>Resultados</h3>
    </div>
    <div class="card-body">
      <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
          <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
              <div class="row no-gutters align-items-center">
                <div class="col mr-2">
                  <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Registros anteriores</div>
                  <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $registrosExistentes }}</div>
                </div>
                <div class="col-auto">
                  <i class="fas fa-users fa-2x text-gray-300"></i>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
          <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
              <div class="row no-gutters align-items-center">
                <div class="col mr-2">
                  <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total registros WS</div>
                  <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $empleados_total }}</div>
                </div>
                <div class="col-auto">
                  <i class="fas fa-users fa-2x text-gray-300"></i>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
          <div class="card border-left-danger shadow h-100 py-2">
            <div class="card-body">
              <div class="row no-gutters align-items-center">
                <div class="col mr-2">
                  <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Registros nuevos</div>
                  <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $empleados_nuevos }}</div>
                </div>
                <div class="col-auto">
                  <i class="fas fa-user-plus fa-2x text-gray-300"></i>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
          <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
              <div class="row no-gutters align-items-center">
                <div class="col mr-2">
                  <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Registros modificados</div>
                  <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $empleados_modificados }}</div>
                </div>
                <div class="col-auto">
                  <i class="fas fa-user-edit fa-2x text-gray-300"></i>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@push('scripts')
@endpush 
@endsection