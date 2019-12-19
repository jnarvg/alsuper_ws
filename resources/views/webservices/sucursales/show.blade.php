@extends('layouts.admin')
@section('title')
Categoria Activo Fijo
@endsection
@section('content')
<div class="content mt-3">
    <div class="card">
        <div class="card-body">
            {{ Form::open(array('action'=>array('CategoriaController@update',$categoria->id_categoria),'method'=>'post','files'=>true)) }}
            <div class="row">
                <div class="col-md-3">
                  <div class="form-group">
                      <label for="categoria">*Categoria</label>
                      <input type="text" name="categoria" id="categoria" value="{{ $categoria->categoria }}"  class="letrasModal form-control" required="true" />
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label for="file-input">Imagen</label>
                    <div class="image-upload">
                      <label for="file-input">
                          <img src="{{ asset('/images/iconos/camara-de-fotos.png') }}" alt ="Click aquí para subir tu foto" class="ico-cam" title ="Click aquí para subir tu foto" > 
                      </label> 
                      <input id="file-input" class="Images" name="file-input" type="file" accept="image/*" capture />
                      <img id="blah-file-input" class="preview" src="{{ asset($categoria->imagen_path) }}" width="30%" alt="" title="" style="padding-left: 10%;" />
                    </div>
                  </div>
                </div>            
                <div class="col-md-3 offset-md-6">
                    <div class="form-group">
                        <a href="{{ route('categoria') }}" class="btn btn-dark btn-block">REGRESAR</a>
                    </div>
                </div> 
                <div class="col-md-3">
                    <div class="form-group">
                        <button type="submit" class="btn btn-info btn-block">GUARDAR</button>
                    </div>
                </div>
            </div>
            {{ Form::close()}}
            <div class="card mt-3">
              <div class="card-header bg-gradient-primary text-white  mb-4">
                <div class="row justify-content-between">
                  <div class="col-md-8">Listado de articulos
                  </div>
                  <div class="col-md-4 mb-0 " align="right">
                    <a href="" class="mb-0 d-sm-inline-block btn-ico-dark text-xl" id="btnplus" data-toggle="modal" data-target="#modal-new"><i class="fas fa-plus"></i></a>
                  </div>
                </div>
              </div>
              <div class="card-body">
                <div class="table-responsive">
                  <table class="table table-hover table-stripered text-sm">
                      <thead>
                          <tr>
                          <th class="center">Id</th>
                          <th class="center">Articulo</th>
                          <th class="center">Acciones</th>
                          </tr>
                      </thead>
                      <tbody>
                          @foreach ($articulo_categoria as $s)
                          <tr>
                              <td class="center">
                                <a href="{{URL::action('ArticuloCategoriaController@show', [$s->id_articulo_categoria, 'categoria-show' ])}}"  >
                                {{ $s->id_articulo_categoria }}</a>
                              </td>
                              <td class="center">
                                <a href="{{URL::action('ArticuloCategoriaController@show', [$s->id_articulo_categoria, 'categoria-show' ])}}"  >{{ $s->articulo}}</a>
                              </td>
                              <td class="center-acciones">
                                <a href="{{URL::action('ArticuloCategoriaController@show', [$s->id_articulo_categoria, 'categoria-show' ])}}"  ><button class="btn-ico" ><i class="fas fa-pencil-alt"></i></button></a>
                                @if (auth()->user()->rol == 3)
                                <a href="#" data-target="#modal-delete{{$s->id_articulo_categoria}}" data-toggle="modal" style="width: 30%;"><button class="btn-ico"><i class="fas fa-trash"></i></button></a>  
                                @endif
                              </td>
                          </tr>
                          @endforeach
                      </tbody>
                  </table>
                </div>
              </div>
              {{$articulo_categoria->render()}}
            </div>       
        </div>
    </div>
</div>

{{-- Preview --}}
<div class="modal collapse" aria-hidden="true" role="dialog" tabindex="-1" id="modal-preview">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header-img">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
      </div>
      <div class="modal-body-img">
        <img id="preview-img" src="" class="modal-img">
      </div>
      <div class="modal-footer-img">
        <h3 id="preview-text"></h3>
      </div>
    </div>
  </div>
</div>
{{-- Nuevo registro hijo--}}
<div class="modal collapse" aria-hidden="true" role="dialog" tabindex="-1" id="modal-new">
  {{ Form::open(array('action'=>array('ArticuloCategoriaController@store'),'method'=>'post','files'=>true)) }}
  <div class="modal-dialog" role="document">
      <div class="modal-content">
          <div class="modal-header">
              <h4 class="modal-title text-dark">Nuevo</h4>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
          </div>
          <div class="modal-body">
              <div class="row">
                  <div class="col-md-12">
                      <div class="form-group">
                          <label for="nuevo_requisito_mdl">*Articulo</label>
                          <input type="text" name="nuevo_requisito_mdl" id="nuevo_requisito_mdl" value=""  class="letrasModal form-control" required="true" />
                      </div>
                  </div>               
              </div>
          </div>
          <div class="modal-footer">
              <button type="button" class="btn btn-dark" data-dismiss="modal">Cerrar</button>
              <button type="submit" class="btn btn-info">Confirmar</button>
          </div>
      </div>
  </div>
  {{ Form::close()}}
</div>
@push('scripts')
<script>
  jQuery(document).ready(function() {
    jQuery(".preview").click(function(){
        
        var srcimagen = jQuery(this).attr('src');
        var tituloimagen = jQuery(this).attr('title');
        jQuery("#preview-img").attr('src',srcimagen);
        jQuery("#preview-img").attr('alt',tituloimagen);
        jQuery("#preview-text").html(tituloimagen);
        jQuery("#modal-preview").modal();
      });
    });
</script>
<script type="application/javascript">
      
    jQuery('input[type=file]').change(function(){
     var filename = jQuery(this).val().split('\\').pop();
     var idname = jQuery(this).attr('id');
     jQuery('span.'+idname).next().find('span').html(filename);
    });
</script>
<script src="{{ asset('js/uploadfotos.js') }}"></script>
@endpush 
@endsection