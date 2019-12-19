<?php

namespace App\Exports;

use App\Prospecto;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
class Rep_EngacheRecibir implements FromView
{

    public function __construct($view, $resultados = "", $total_enganche, $total_pagado = "")
    { 
        $this->view = $view;
        $this->resultados = $resultados;
        $this->total_pagado = $total_pagado;
        $this->total_enganche = $total_enganche;
    }
    public function view(): View
    {
        return view('exports.rep_enganche_recibir', [
            'resultados' => $this->resultados,
            'total_pagado' => $this->total_pagado,
            'total_enganche' => $this->total_enganche,
        ]);
    }
}
