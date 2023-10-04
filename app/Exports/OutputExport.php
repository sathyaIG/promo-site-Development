<?php

namespace app\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\BeforeExport;
use Maatwebsite\Excel\Events\AfterSheet;
use App\Models\Admin\SessionForm;
use DB;
use Exception;

class OutputExport implements WithEvents, FromView
{

    protected $datas;
    protected $promoType;

    function __construct($datas,$promoType)
    {
        $this->datas = $datas;
        $this->promoType = $promoType;
    }

    public function registerEvents(): array
    {
        try{
            if( $this->promoType == 1) {
                return [
                    BeforeExport::class  => function (BeforeExport $event) {
        
        
        
                        $event->writer->getDelegate()->getSecurity()->setLockWindows(true);
                        $event->writer->getDelegate()->getSecurity()->setLockStructure(true);
                        $event->writer->getDelegate()->getSecurity()->setWorkbookPassword("Your password");
                    },
                    AfterSheet::class    => function (AfterSheet $event) {
        
                        $row = 2; // Starting row (D1)
                        // dd($this->datas);
                        foreach ($this->datas as $data) {
                            // Split the EC ID values by comma and add line breaks
                            $ecIds = str_replace(',', ",\n", $data['ecId']);
                            // $saIds = str_replace(',', ",'\n", $data['saCityId']);
                            $event->sheet->setCellValueByColumnAndRow(4, $row++, $ecIds); // Column D
                            // $event->sheet->setCellValueByColumnAndRow(6, $row++, $saIds);
                        }

                        // $row = 2; // Starting row (D1)
                        // // dd($this->datas);
                        // foreach ($this->datas as $data) {
                        //     // Split the EC ID values by comma and add line breaks
                        //     $saIds = str_replace(',', ",'\n", $data['saCityId']);
                        //     $event->sheet->setCellValueByColumnAndRow(6, $row++, $saIds);
                        // }
                        // if($this->promoType == 1){
                        //     $column = 'A1:AL1';
                        // }else{
                        //     $column = 'A1:AN1';
                        // }
                        $event->sheet->getStyle('D1:D' . ($row - 1))->getAlignment()->setWrapText(true);
                        $event->sheet->getStyle('F1:F' . ($row - 1))->getAlignment()->setWrapText(true);
        
                        $event->sheet->getDelegate()->getStyle('A1:Q1')
                            ->getFont()
                            ->setBold(true);
        
                        $event->sheet->getDelegate()->getStyle('A1:Q1')
                            ->getFill()
                            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setARGB('FF999B');
                        $event->sheet->getDelegate()->getStyle('A1:Q1')
                            ->getFont()
                            ->setBold(true)
                            ->setSize(10);
                    },
                ];
            }else{
                return [
                    BeforeExport::class  => function (BeforeExport $event) {
        
        
        
                        $event->writer->getDelegate()->getSecurity()->setLockWindows(true);
                        $event->writer->getDelegate()->getSecurity()->setLockStructure(true);
                        $event->writer->getDelegate()->getSecurity()->setWorkbookPassword("Your password");
                    },
                    AfterSheet::class    => function (AfterSheet $event) {
                               
        
                        $event->sheet->getDelegate()->getStyle('A1:H1')
                            ->getFont()
                            ->setBold(true);
        
                        $event->sheet->getDelegate()->getStyle('A1:H1')
                            ->getFill()
                            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setARGB('FF999B');
                        $event->sheet->getDelegate()->getStyle('A1:H1')
                            ->getFont()
                            ->setBold(true)
                            ->setSize(10);
                    },
                ];
            }
           
        }catch (Exception $ex){
            dd($ex);
        }
       
      
    }

    public function view(): View
    {
        if($this->promoType == 1){
            return view('exports.output',[
                'details' => $this->datas
            ]);   
        }else{
            return view('exports.output_combo',[
                'details' => $this->datas
            ]);   
        }
           
    }
}
