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

class RejectedExport implements WithEvents, FromView
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
            // dd($this->datas);
            return view('exports.reject',[
                'details' => $this->datas
            ]);   
        }else{
            return view('exports.output_combo',[
                'details' => $this->datas
            ]);   
        }
           
    }
}
