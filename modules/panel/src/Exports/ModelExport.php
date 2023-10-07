<?php

namespace Fpaipl\Panel\Exports;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use PhpOffice\PhpSpreadsheet\Style\Style;
use Maatwebsite\Excel\Concerns\WithDefaultStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;


class ModelExport implements FromArray, WithHeadings, WithDefaultStyles,  WithEvents, ShouldAutoSize
{
    use Exportable;
    public $model = '';
    public $datatable = '';
    public $fields;

    public function __construct($model, $datatable)
    {
        $this->model = $model;
        $this->datatable = $datatable;
        $this->fields = (new  $this->datatable())->getfields()->toArray();
    }

    public function array(): array
    {
        $imporTableModel = array();
        $sampleModel = $this->model::sampleModel();
        foreach ($this->fields as $key => $field) {
            if ($field['importable']) {
                array_push($imporTableModel, $sampleModel->$key);
            }
        }
        return [$imporTableModel];
    }

     /*
        It returns the primary image of a record.
    */
    // public function getSingleMedia($model)
    // {
    //     $image = '';
    //     $image = $model->getImage('s100', 'primary');
    //     return $image;
    // }

    /*
        It returns the secondary images of a record.
    */
    // public function getMultipleMedias($model)
    // {
    //     $images = '';
    //     foreach ($model->getImage('s400', 'secondary', true) as $mediaItemUrl) {
    //         if ($images != '') {
    //             $images .= "," . $images;
    //         } else {
    //             $images = $mediaItemUrl;
    //         }
    //     }
    //     return $images;
    // }

    public function headings(): array
    {
        $ImportableFieldsName = [];
        foreach ($this->fields as $field) {
            if ($field['importable']) {
                array_push($ImportableFieldsName, $field['labels']['export']);
            }
        }
        return $ImportableFieldsName;
    }

    public function defaultStyles(Style $defaultStyle)
    {
        // Configure the default styles
        return $defaultStyle->getFont()->setSize(14);
        
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            1    => ['font' => ['bold' => true, 'size' => 12]],
            
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class    => function(AfterSheet $event) {
  
                $event->sheet->getDelegate()->getStyle('A1:Z1')
                        ->getFill()
                        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setARGB('EDE4E3');
  
            },
        ];
    }
    
    // public function columnWidths(): array
    // {
    //     return [
    //         'A' => 20,
    //         'B' => 20,
    //         'C' => 20,
    //         'D' => 20,
    //     ];
    // }
}
