<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
      if (auth()->user()->rol != 5) {
        $mes = date('m');
        $hoy = date('Y-m-d');
        $year = date('Y');

        ///echo "prospectos: ".$prospecto_prospecto->contador;
        return view('welcome',compact('prospecto_prospecto','prospecto_postergado','prospecto_apartado','prospecto_perdido','prospecto_noescriturado','prospecto_escriturado','prospecto_pagando','prospecto_contrato','resultados'));
        
      }elseif (auth()->user()->rol == 5) {
        $idprospecto = auth()->user()->prospecto_id;

        $colores_A = array("#AFEEEE","#7FFFD4","#40E0D0","#48D1CC","#00CED1","#5F9EA0","#4682B4","#B0C4DE","#B0E0E6","#ADD8E6","#87CEEB","#87CEFA","#00BFFF","#1E90FF","#6495ED","#7B68EE","#4169E1","#0000FF","#00008B","#000080","#191970");

        return view('externos.show',compact('prospecto','documentos','contactos','plazos_pago','tipos_operacion','resultados','colores_A'));
      }
    }
    public function catalogos()
    {
        return view('catalogos.index');
    }

    public function reportes()
    {
        return view('reportes.index');
    }
}
