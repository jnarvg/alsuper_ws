<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests; 
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use DB;
use App\Http\Middleware\Authenticate;

class CatalogosAjaxController extends Controller
{
    //
    public function CatalogosPropiedades($id){
        $propiedadesPrecios = DB::table('propiedad as p') /*Propiedad*/
        ->join('moneda','p.moneda','=','id_moneda','left',false)
        ->select('p.nombre','p.precio','p.enganche','p.moneda','siglas')
        ->where('id_propiedad','=',$id)
        ->get();
        return response()->json($propiedadesPrecios);
    }
    public function CatalogosColores($id){
        $catalogosColores = DB::table('color as p') /*Propiedad*/
        ->select('p.id_color','p.color','p.codigo_hexadecimal')
        ->where('id_color','=',$id)
        ->get();
        return response()->json($catalogosColores);
    }

    public function CatalogosEstados($id){
        $catalogosEstados = DB::table('estado') /*Estados*/
        ->select('id_estado','estado')
        ->where('pais_id','=',$id)
        ->get();
        return response()->json($catalogosEstados);
    }
    public function CatalogosCiudades($id){
        $catalogosCiudades = DB::table('ciudad') /*Estados*/
        ->select('id_ciudad','ciudad')
        ->where('estado_id','=',$id)
        ->get();
        return response()->json($catalogosCiudades);
    }
    public function CatalogosNiveles($id){
        $catalogoNiveles = DB::table('nivel') /*Estados*/
        ->select('id_nivel','nivel') /**/
        ->where('proyecto_id','=',$id)
        ->get();
        return response()->json($catalogoNiveles);
    }
    public function CatalogosProyectos($id){
        $catalogosProyectos = DB::table('proyecto as p') /*Estados*/
        ->join('pais','p.pais_id','=','id_pais','left',false)
        ->join('estado','p.estado_id','=','id_estado','left',false)
        ->join('ciudad','p.ciudad_id','=','id_ciudad','left',false)
        ->select('p.nombre','p.pais_id','p.estado_id','p.ciudad_id','ciudad','estado','pais','direccion')
        ->where('id_proyecto','=',$id)
        ->get();
        return response()->json($catalogosProyectos);
    }
    public function CatalogosPropiedadesDesarrollo($id){

        if ($id == 'sin proyecto') {
            $propiedadesDesarrollo = DB::table('propiedad as p') /*Propiedad*/
            ->join('estatus_propiedad as e','p.estatus_propiedad_id','=','e.id_estatus_propiedad') /*Estatus propiedad*/
            ->join('tipo_propiedad as t','p.tipo_propiedad_id','=','t.id_tipo_propiedad') /*Tipo propiedad*/
            ->select('p.id_propiedad','p.nombre','e.estatus_propiedad')
            ->where('p.proyecto_id','=',null)
            ->where('t.tipo_propiedad','=','Tipo propiedad')
            ->whereIn('e.estatus_propiedad',['Disponible','Bloqueado'])
            ->orderBy('p.nombre','asc')
            ->get();
        }else{     
            $propiedadesDesarrollo = DB::table('propiedad as p') /*Propiedad*/
            ->join('estatus_propiedad as e','p.estatus_propiedad_id','=','e.id_estatus_propiedad') /*Estatus propiedad*/
            ->join('tipo_propiedad as t','p.tipo_propiedad_id','=','t.id_tipo_propiedad') /*Tipo propiedad*/
            ->select('p.id_propiedad','p.nombre','e.estatus_propiedad')
            ->where('p.proyecto_id','=',$id)
            ->where('t.tipo_propiedad','=','Tipo unidad')
            ->whereIn('e.estatus_propiedad',['Disponible','Bloqueado'])
            ->orderBy('p.nombre','asc')
            ->get();
        }

        return response()->json($propiedadesDesarrollo);
    }
    public function CatalogosPropiedadesDesarrolloSinEstatus($id){

        if ($id == 'sin proyecto') {
            $propiedadesDesarrollo = DB::table('propiedad as p') /*Propiedad*/
            ->join('estatus_propiedad as e','p.estatus_propiedad_id','=','e.id_estatus_propiedad') /*Estatus propiedad*/
            ->join('tipo_propiedad as t','p.tipo_propiedad_id','=','t.id_tipo_propiedad') /*Tipo propiedad*/
            ->select('p.id_propiedad','p.nombre','e.estatus_propiedad')
            ->where('p.proyecto_id','=',null)
            ->where('t.tipo_propiedad','=','Tipo propiedad')
            ->orderBy('p.nombre','asc')
            ->get();
        }else{     
            $propiedadesDesarrollo = DB::table('propiedad as p') /*Propiedad*/
            ->join('estatus_propiedad as e','p.estatus_propiedad_id','=','e.id_estatus_propiedad') /*Estatus propiedad*/
            ->join('tipo_propiedad as t','p.tipo_propiedad_id','=','t.id_tipo_propiedad') /*Tipo propiedad*/
            ->select('p.id_propiedad','p.nombre','e.estatus_propiedad')
            ->where('p.proyecto_id','=',$id)
            ->where('t.tipo_propiedad','=','Tipo unidad')
            ->orderBy('p.nombre','asc')
            ->get();
        }

        return response()->json($propiedadesDesarrollo);
    }
    public function CatalogosCliente($id)
    {
        $prospecto = DB::table('prospectos as p')
        ->join('estatus_crm as e','p.estatus','=','e.id_estatus_crm','left',false)
        ->join('propiedad as prop','p.propiedad_id','=','prop.id_propiedad','left',false)
        ->join('proyecto as py','p.proyecto_id','=','py.id_proyecto','left',false)
        ->join('medio_contacto as mc','p.medio_contacto_id','=','mc.id_medio_contacto','left',false)
        ->join('motivo_perdida as mp','p.motivo_perdida_id','=','mp.id_motivo_perdida','left',false)
        ->join('users as u','p.asesor_id','=','u.id','left',false)
        ->join('esquema_comision as ec','p.esquema_comision_id','=','ec.id_esquema_comision','left',false)
        ->select('p.id_prospecto', 'p.nombre','rfc','correo','telefono','telefono_adicional','p.asesor_id','p.propiedad_id','p.proyecto_id','folio','p.estatus','p.fecha_registro','medio_contacto_id', 'e.estatus_crm as estatus_prospecto', 'prop.nombre as nombre_propiedad','py.nombre as nombre_proyecto','mc.medio_contacto','u.name as nombre_agente', 'fecha_recontacto','observaciones','fecha_visita','fecha_cotizacion','fecha_apartado','monto_apartado','fecha_venta','monto_venta','fecha_enganche','monto_enganche','tipo_operacion_id','motivo_perdida_id','cerrador','num_plazos','fecha_ultimo_pago','monto_ultimo_pago','fecha_escrituracion','motivo_perdida','prop.precio','prop.enganche','p.esquema_comision_id','ec.esquema_comision','p.comision_id','extension','e.nivel')
        ->where('id_prospecto','=',$id)
        ->get();
        return response()->json($prospecto);
    }
    public function CatalogosBancos($id){
        $catalogosBancos = DB::table('banco') /*Estados*/
        ->select('id_banco','banco','rfc')
        ->where('id_banco','=',$id)
        ->get();
        return response()->json($catalogosBancos);
    }
    /*Para los iconos de laertas en el ayout d emensajes y actividades*/
    public function actividades_hoy(){
        $hoy = date('Y-m-d');
        $resultados = DB::table('actividad') /*Estados*/
        ->select('id_actividad','titulo','fecha','hora','tipo_actividad') /**/
        ->where('agente_id','=',auth()->user()->id)
        ->where('estatus','!=','Completada')
        ->where('fecha_recordatorio','>=',$hoy)
        ->orderBy('fecha','ASC')
        ->limit(5)
        ->get();

        return response()->json($resultados);
    }
    public function mensajes_nuevos(){
        $catalogoNiveles = DB::table('nivel') /*Estados*/
        ->select('id_nivel','nivel') /**/
        ->where('proyecto_id','=',$id)
        ->get();
        return response()->json($catalogoNiveles);
    }
    public function CatalogosCotizacionesContrato($prospecto){
        $cotizacion = DB::table('cotizacion') /*Estados*/
        ->select('id_cotizacion') /**/
        ->where('prospecto_id','=',$prospecto)
        ->orderBy('fecha_cotizacion','DESC')
        ->first();
        $prospecto = DB::table('prospectos')->select('propiedad_id')
        ->where('id_prospecto', $prospecto)->first();

        $cotizacion_detalle = DB::table('detalle_cotizacion') /*Estados*/
        ->select('id_detalle_cotizacion','propiedad_id','precio_propiedad', 'plazos', 'inicial_a', 'contraentrega_a', 'mensualidades_a', 'descuento_a', 'inicial_b', 'contraentrega_b', 'mensualidades_b', 'descuento_b', 'inicial_c', 'contraentrega_c', 'mensualidades_c', 'descuento_c', 'monto_inicial_d', 'inicial_d', 'contraentrega_d', 'mensualidades_d', 'descuento_d','porcentaje_total_d','adicionales_d', 'porcentaje_inicial_d', 'porcentaje_contraentrega_d', 'porcentaje_mensualidades_d', 'porcentaje_adicionales_d', 'porcentaje_descuento_d', 'porcentaje2_total_d')
        ->where('cotizacion_id','=',$cotizacion->id_cotizacion)
        ->where('propiedad_id','=',$prospecto->propiedad_id)
        ->get();

        return response()->json($cotizacion_detalle);
    }

}
