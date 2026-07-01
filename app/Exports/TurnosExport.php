<?php
namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class TurnosExport implements FromView, ShouldAutoSize, WithStyles, WithEvents
{
    protected $datosProcesados;

    public function __construct($datosProcesados)
    {
        $this->datosProcesados = $datosProcesados;
    }

    public function view(): View
    {
        return view('excel.reporte-turnos', [
            'datos' => $this->datosProcesados
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        $bgPrimary = 'FF1E3A8A';
        $bgSecondary = 'FF3B82F6';
        $textWhite = 'FFFFFFFF';
        $textGray = 'FF334155';

        $highestColumn = $sheet->getHighestColumn();
        $horasCount = count($this->datosProcesados['matriz_tiempos']);

        $rowPersonasHeader = 13 + $horasCount;
        $rowPersonasSubHeader = 14 + $horasCount;
        $rowPersonasTotal = 15 + 2 * $horasCount;

        $sheet->getStyle('A1:' . $highestColumn . $rowPersonasTotal)->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ]
        ]);

        $borderStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ];

        $sheet->getStyle('A5:' . $highestColumn . '6')->applyFromArray($borderStyle); // Total Turnos
        $sheet->getStyle('A8:' . $highestColumn . (11 + $horasCount))->applyFromArray($borderStyle); // Tiempos
        $sheet->getStyle('A' . $rowPersonasHeader . ':' . $highestColumn . $rowPersonasTotal)->applyFromArray($borderStyle); // Personas

        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            2 => ['font' => ['bold' => true, 'color' => ['argb' => $textGray]]],
            3 => ['font' => ['bold' => true, 'color' => ['argb' => $textGray]]],

            5 => ['font' => ['bold' => true, 'color' => ['argb' => $textWhite]], 'fill' => ['fillType' => 'solid', 'color' => ['argb' => $bgPrimary]]],
            6 => ['font' => ['bold' => true, 'size' => 13]],

            8 => ['font' => ['bold' => true, 'color' => ['argb' => $textWhite]], 'fill' => ['fillType' => 'solid', 'color' => ['argb' => $bgPrimary]]],
            9 => ['font' => ['bold' => true, 'color' => ['argb' => $textWhite]], 'fill' => ['fillType' => 'solid', 'color' => ['argb' => $bgPrimary]]],
            10 => ['font' => ['bold' => true, 'color' => ['argb' => $textWhite]], 'fill' => ['fillType' => 'solid', 'color' => ['argb' => $bgSecondary]]],
            (11 + $horasCount) => ['font' => ['bold' => true]], // Fila Promedio

            $rowPersonasHeader => ['font' => ['bold' => true, 'color' => ['argb' => $textWhite]], 'fill' => ['fillType' => 'solid', 'color' => ['argb' => $bgPrimary]]],
            $rowPersonasSubHeader => ['font' => ['bold' => true, 'color' => ['argb' => $textWhite]], 'fill' => ['fillType' => 'solid', 'color' => ['argb' => $bgSecondary]]],
            $rowPersonasTotal => ['font' => ['bold' => true]], 
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $cantidadTipos = count($this->datosProcesados['tipos_turno']);

                // Vertical vs Horizontal
                if ($cantidadTipos > 5) {
                    $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
                } else {
                    $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_PORTRAIT);
                }

                $sheet->getPageSetup()->setFitToWidth(1);
                $sheet->getPageSetup()->setFitToHeight(0);

                $sheet->getPageSetup()->setHorizontalCentered(true);
                
                $sheet->getPageMargins()->setTop(0.75);
                $sheet->getPageMargins()->setRight(0.5);
                $sheet->getPageMargins()->setLeft(0.5);
                $sheet->getPageMargins()->setBottom(0.75);
            },
        ];
    }
}