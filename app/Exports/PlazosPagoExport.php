<?php

namespace App\Exports;

use App\PlazosPago;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class PlazosPagoExport implements FromView
{
    public function __construct($view, $resultados = "", $campos = "")
    { 
        $this->view = $view;
        $this->resultados = $resultados;
        $this->campos = $campos;
    }
    public function view(): View
    {
        return view('exports.plazos_pago', [
            'resultados' => $this->resultados,
            'campos' => $this->campos,
        ]);
    }
}
