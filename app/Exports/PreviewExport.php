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

class PreviewExport implements WithEvents, FromView
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
       
        return [
            BeforeExport::class  => function (BeforeExport $event) {



                $event->writer->getDelegate()->getSecurity()->setLockWindows(true);
                $event->writer->getDelegate()->getSecurity()->setLockStructure(true);
                $event->writer->getDelegate()->getSecurity()->setWorkbookPassword("Your password");
            },
            AfterSheet::class    => function (AfterSheet $event) {
                if($this->promoType == 1){
                    $column = 'A1:AL1';
                }else{
                    $column = 'A1:AN1';
                }
                $event->sheet->getDelegate()->getStyle($column)
                    ->getFont()
                    ->setBold(true);

                $event->sheet->getDelegate()->getStyle($column)
                    ->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB('92BB50');
                $event->sheet->getDelegate()->getStyle($column)
                    ->getFont()
                    ->setBold(true)
                    ->setSize(10);
            },
        ];
    }

    public function view(): View
    {
        if($this->promoType == 1){
            return view('exports.preview', [
                'details' => $this->datas
            ]);
        }else if($this->promoType == 2){
            return view('exports.preview_combo', [
                'details' => $this->datas
            ]);
        }else{
            return view('exports.preview_cart_level', [
                'details' => $this->datas
            ]);
        }
       
    }
}
