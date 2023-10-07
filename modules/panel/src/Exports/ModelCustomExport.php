<?php

namespace Fpaipl\Panel\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithDefaultStyles;
use PhpOffice\PhpSpreadsheet\Style\Style;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

use Carbon;

class ModelCustomExport implements FromCollection, WithHeadings, WithStyles, WithDefaultStyles, WithEvents, ShouldAutoSize
{
    public $count;
    public $datas;
    public $selectedColumns=[];
    public $collectionArray=[];
    public $fields;
    public $model = '';
    public $datatable = '';

    /*
        $datas : It holds the all records
        $selectedColumns : It holds the selected columns that we want to export
    */
    public function __construct($model, $datatable, $datas, $selectedColumns){
        $this->count=0;
        $this->datas=$datas;
        $this->selectedColumns=$selectedColumns;
        $this->model = $model;
        $this->datatable = $datatable;
        $this->fields = (new $this->datatable)->getfields()->toArray();
    }

    public function dataPusher($key,$value){
        $this->collectionArray[$key] =$value;
    }

    

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        
        $newDataCollection=[];

        $newDataCollection = $this->datas->map(function($data, $key) {
            foreach($data->getAttributes() as $key => $value){

                $this->dataPusher($key, $data->{$this->fields[$key]['exportable']['value']}($key));
            }

            return $this->collectionArray;

        });

        return $newDataCollection;
    }

    public function headings(): array
    {
        return $this->selectedColumns;
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
            1    => ['font' => ['bold' => true,'size' => 12]],
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
    //         'E' => 20,
    //         'F' => 20,
    //         'G' => 20,
                 
    //     ];
    // }
}