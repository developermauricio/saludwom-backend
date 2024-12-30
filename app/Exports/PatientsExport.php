<?php

namespace App\Exports;

use App\Models\Patient;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class PatientsExport implements FromArray, WithHeadings, ShouldAutoSize, WithEvents
{
    use Exportable;

    private $dataPatients;

    public function __construct(array $dataPatients)
    {
        $this->dataPatients = $dataPatients;
    }


    public function array(): array
    {
        return array_map(function ($patient) {

            $country = 'No Registra';
            $city = 'No Registra';


            if (isset($patient['city'])) {
                $city = $patient['city']['name'];
                $country = $patient['city']['country']['name'];
            }

            Log::info($patient['country']);

            return [
                '#' => $patient['index'],
                'Nombre' => $patient['name'],
                'Correo Electrónico' => $patient['email'],
                'Teléfono' => $patient['phone'],
                'Documento' => $patient['document'],
                'Tipo de Documento' => $patient['documentType']['name'],
                'Estado' => $patient['state'] === '1' ? 'Activo' : 'Inactivo',
                'Género' => $patient['gender']['name'],
                'Fecha de Nacimiento' => $patient['birthday'],
                'Edad' => $patient['age'],
                'País' => $country,
                'Ciudad' => $city,
                'Tipo de Paciente' => $patient['patientType'] === 'courtesy' ? 'Cortesia' : 'Cliente',
                'Fecha de Registro' => Carbon::parse($patient['created_at'])->toDateString(),
            ];
        }, $this->dataPatients);
    }

    public function headings(): array
    {
        return [
            '#',
            'Nombre',
            'Correo Electrónico',
            'Teléfono',
            'Documento',
            'Tipo de Documento',
            'Estado',
            'Género',
            'Fecha de Nacimiento',
            'Edad',
            'País',
            'Ciudad',
            'Tipo de Paciente',
            'Fecha de Registro',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getStyle('A1:N1')->applyFromArray([
                    'font' => [
                        'bold' => true
                    ]
                ]);
            }
        ];
    }
}
