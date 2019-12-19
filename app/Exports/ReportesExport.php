<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ReportesExport implements FromView
{
    public function __construct($view, $resultados = "", $campos = "")
    { 
        $this->view = $view;
        $this->resultados = $resultados;
        $this->campos = $campos;
    }
    public function view(): View
    {
        return view('exports.reportes', [
            'resultados' => $this->resultados,
            'campos' => $this->campos,
        ]);
    }
}
