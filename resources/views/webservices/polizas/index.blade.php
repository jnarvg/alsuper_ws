@extends('layouts.admin')
@section('title')
Webservice - Polizas
@endsection
@section('filter')
  <a href="#" data-toggle="modal" data-target="#modal-uso_propiedad"><button class="mb-0 d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i class="fas fa-plus"></i></button></a>
@endsection
@section('content')
<div class="content mt-3">
  <div class="card">
    <div class="card-body">
      {{ Form::open(array('action'=>array('PolizaWSController@ws_poliza'),'method'=>'get')) }}
        <div class="row">
          <div class="col-md-3">
            <div class="form-group">
              <label for="empresa_id">Empresa</label>
              <select class="form-control" id="empresa_id"  name="empresa_id">}
                @foreach ($empresas as $e)
                  @if ($e->id_empresa == $request->empresa_id)
                  <option selected value="{{ $e->id_empresa }}">{{ $e->nombre_comercial }}</option>
                  @else
                  <option value="{{ $e->id_empresa }}">{{ $e->nombre_comercial }}</option>
                  @endif
                @endforeach
              </select>
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
              <label for="fecha_inicio">Fecha inicio</label>
              <input type="date" name="fecha_inicio" id="fecha_inicio" value="{{ $request->fecha_inicio }}" class="form-control">
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
              <label for="fecha_fin">Fecha fin</label>
              <input type="date" name="fecha_fin" id="fecha_fin" value="{{ $request->fecha_fin }}" class="form-control">
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
              <label for="tipo_poliza">Tipo de poliza</label>
              <select class="form-control" id="tipo_poliza"  name="tipo_poliza">
                @foreach ($tipos_polizas as $e)
                  @if ($e[0] == $request->tipo_poliza)
                  <option selected value="{{ $e[0] }}">{{ $e[1] }}</option>
                  @else
                  <option value="{{ $e[0] }}">{{ $e[1] }}</option>
                  @endif
                @endforeach
              </select>
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
              <label for="fecha_poliza_generador">Fecha poliza</label>
              <input type="date" name="fecha_poliza_generador" id="fecha_poliza_generador" value="{{ $request->fecha_poliza_generador }}" class="form-control">
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
              <button class="btn btn-primary btn-block" type="submit" style="margin-top: 2rem;">Enviar</button>
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
                  <div class="text-xs font-weight-bold text-info text-uppercase mb-1">RECCNO anterior</div>
                  <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $reccnoanterior }}</div>
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
                  <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $polizasinsertadas }}</div>
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
                  <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Ultimo RECNNO</div>
                  <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $reccnoultimo }}</div>
                </div>
                <div class="col-auto">
                  <i class="fas fa-user-plus fa-2x text-gray-300"></i>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="row mt-3">
        <div class="col-md-12">
          <h3>Evidencia poliza</h3>
        </div>
        <div class="table-responsive">
          <table class="table table-hover table-stripped">
            <thead>
              <th>Tipo poliza</th>
              <th>D o C</th>
              <th>p13_VALOR</th>
              <th>p12_HIST</th>
              <th>p19_CTCD</th>
              <th>p20_CTAD</th>
              <th>p31_RECNO</th>
              <th>FECHA</th>
            </thead>
            <tbody>
              @if (count($evidencia)> 0)
                @foreach ($evidencia as $e)
                  <tr>
                    <td>{{ $e->tipo_poliza }}</td>
                    <td>{{ $e->p5_DC }}</td>
                    <td>{{ $e->p13_VALOR }}</td>
                    <td>{{ $e->p12_HIST }}</td>
                    <td>{{ $e->p19_CTCD }}</td>
                    <td>{{ $e->p20_CTAD }}</td>
                    <td>{{ $e->p31_RECNO }}</td>
                    <td>{{ $e->p2_DATA }}</td>
                  </tr>
                @endforeach
              @endif
            </tbody>
          </table>
          {{$evidencia->render()}}
        </div>
        <div class="col-md-12 mt-3">
          <h3>Evidencia poliza</h3>
        </div>
        <div class="table-responsive">
          <table class="table table-hover table-stripped">
            <thead>
              <th>Tipo poliza</th>
              <th>D o C</th>
              <th>p5_VALOR</th>
              <th>p7_BANCO</th>
              <th>p14_BENEF</th>
              <th>p15_HISTOR</th>
              <th>p22_NUMERO</th>
              <th>p97_RECNO</th>
              <th>p100_RFC</th>
              <th>FECHA</th>
            </thead>
            <tbody>
              @if (count($evidencia_financiero)> 0)
                @foreach ($evidencia_financiero as $e)
                  <tr>
                    <td>{{ $e->tipo_poliza }}</td>
                    <td>{{ $e->p5_DC }}</td>
                    <td>{{ $e->p5_VALOR }}</td>
                    <td>{{ $e->p7_BANCO }}</td>
                    <td>{{ $e->p14_BENEF }}</td>
                    <td>{{ $e->p15_HISTOR }}</td>
                    <td>{{ $e->p22_NUMERO }}</td>
                    <td>{{ $e->p31_RECNO }}</td>
                    <td>{{ $e->p2_DATA }}</td>
                  </tr>
                @endforeach
              @endif
            </tbody>
          </table>
          {{$evidencia_financiero->render()}}
        </div>
      </div>
    </div>
  </div>
</div>

@push('scripts')
@endpush 
@endsection