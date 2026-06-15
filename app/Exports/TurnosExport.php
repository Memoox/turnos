<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class TurnosExport implements FromView, ShouldAutoSize
{
    protected $datos;

    // Recibimos toda la data ya procesada desde el controlador
    public function __construct($datos)
    {
        $this->datos = $datos;
    }

    public function view(): View
    {
        // Le pasamos la data a una vista Blade que vamos a crear
        return view('excel.reporte-turnos', [
            'datos' => $this->datos
        ]);
    }
}