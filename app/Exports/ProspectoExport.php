<?php

namespace App\Exports;

use App\Prospecto;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ProspectoExport implements FromView
{
    public function __construct($view, $resultados = "", $campos = "")
    { 
        $this->view = $view;
        $this->resultados = $resultados;
        $this->campos = $campos;
    }
    public function view(): View
    {
        return view('exports.prospectos', [
            'resultados' => $this->resultados,
            'campos' => $this->campos,
        ]);
    }
}
