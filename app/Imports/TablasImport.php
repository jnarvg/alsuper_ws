<?php

namespace App\Imports;

use DB;
use Auth;

use App\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;
use Illuminate\Support\Facades\Storage;

class TablasImport implements ToModel, WithHeadingRow
{
    public function __construct($tabla, $tipo_importacion = "", $campos = "", $user = "", $campo_llave = "")
    { 
        $this->tabla = $tabla;
        $this->tipo_importacion = $tipo_importacion;
        $this->campos = $campos;
        $this->user = $user;
        $this->campo_llave = $campo_llave;
    }

    public function model(array $row)
    {
      //HeadingRowFormatter::default('none');
      $success =0;
      $Fail_Requeridos = 0;

      ///
      if ($this->tipo_importacion == 'Actualizar') {
        $campos_c = DB::table('campos_configuracion')
        ->where('tabla', $this->tabla)
        ->where('actualizable', 'SI')
        ->get();
        $request = $this->campos;
        if ($this->campo_llave != null) {

          if ( $this->tabla == 'prospectos' ) {
             $array_campos['asesor_id'] = $this->user;
          }elseif($this->tabla == 'propiedad'){
            $array_campos['tipo_propiedad_id'] = 1;
          }
          foreach ($campos_c as $k) {
            $indice = $request[$k->campo];
            
            if ($k->requerido == 'SI' and $indice == null) {
              $Fail_Requeridos = $Fail_Requeridos + 1;
            }else{
              if ( $indice != null and !empty($indice) ) {
                $success = $success + 1;
                $indice_excel = strtolower( str_replace(' ', '_', $indice));
                if ($k->fk_tabla != null) {
                  $valor_insertar = $this->FindFK($row[$indice_excel], $k->fk_tabla, $k->fk_campo, $k->fk_pk);
                }else{
                  $valor_insertar = $row[$indice_excel];
                }
                if ($k->campo == $this->campo_llave) {
                  $elemento_llave_search = $valor_insertar;
                }
                $array_campos[$k->campo] = $valor_insertar;
              }
            }
          }
          $array_register[] = $array_campos;
          Storage::append('file.log', 'Appended Text----------------'.json_encode($array_campos));
          DB::table($this->tabla)->where($this->campo_llave, $elemento_llave_search)->update($array_campos);
        }

      }else{///INSRTAR NUEVO
        $campos_c = DB::table('campos_configuracion')
        ->where('tabla', $this->tabla)
        ->where('importable', 'SI')
        ->get();
        $request = $this->campos;
        $array_campos['fecha_registro'] = date('Y-m-d H:i');
        if ( $this->tabla == 'prospectos' ) {
           $array_campos['asesor_id'] = $this->user;
           $array_campos['estatus'] = 1;
        }elseif($this->tabla == 'propiedad'){
          $array_campos['tipo_propiedad_id'] = 1;
        }
        foreach ($campos_c as $k) {
          $indice = $request[$k->campo];
          if ($k->requerido == 'SI' and $indice == null) {
            $Fail_Requeridos = $Fail_Requeridos + 1;
          }else{
            if ( $indice != null and !empty($indice) ) {
              $success = $success + 1;
              $indice_excel = strtolower( str_replace(' ', '_', $indice));
              if ($k->fk_tabla != null) {
                $valor_insertar = $this->FindFK($row[$indice_excel], $k->fk_tabla, $k->fk_campo, $k->fk_pk);
              }else{
                $valor_insertar = $row[$indice_excel];
              }

              $array_campos[$k->campo] = $valor_insertar;
            }
          }
        }
        $array_register[] = $array_campos;

        DB::table($this->tabla)->insert($array_register);

      }
    }

    public function FindFK($valor_excel='', $tabla_fk ='', $campo_fk = '', $pk = '')
    {
      $FK = DB::table($tabla_fk)->where($campo_fk, $valor_excel)->value($pk);
      return $FK;
    }
}
