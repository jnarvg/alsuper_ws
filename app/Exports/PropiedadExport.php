<?php

namespace App\Exports;

use App\Propiedad;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class PropiedadExport implements FromView
{
    public function __construct($view, $resultados = "", $campos = "")
    { 
        $this->view = $view;
        $this->resultados = $resultados;
        $this->campos = $campos;
    }
    public function view(): View
    {
        return view('exports.propiedad', [
            'resultados' => $this->resultados,
            'campos' => $this->campos,
        ]);
    }
}
