<div class="modal fade" aria-hidden="true" role="dialog" tabindex="-1" id="modal-delete{{$r->id_categoria}}">
    {{ Form::open(array('action'=>array('CategoriaController@destroy',$r->id_categoria),'method'=>'get')) }}
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Eliminar</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Esta seguro que desea eliminar este registro?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn btn-info">Confirmar</button>
            </div>
        </div>
    </div>
    {{ Form::close()}}
    
</div>