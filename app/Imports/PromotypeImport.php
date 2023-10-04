<?php

namespace App\Imports;

use App\Models\Admin\CategoryTLC;

use App\Models\Admin\CategoryBrand;
use App\Models\Admin\CategoryDept;
use App\Models\Admin\UserRole;
use App\Models\Admin\ManufacturerMaster;
use App\Models\User;
use DB;
use Auth;
use Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use App\Jobs\SetpasswordJob;
use App\Models\Admin\CampaignBusinessType;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Carbon\Carbon;

// class PromotypeImport implements ToCollection, WithChunkReading, ShouldQueue, WithStartRow
class PromotypeImport 
{

    protected $user_id;
    protected $log_id;

    public function __construct($user_id, $log_id)
    {

        $this->user_id = $user_id;
        $this->log_id = $log_id;
    }

    public function collection(Collection $rows)
    {


    }

    public function startRow(): int
    {
        return 1;
    }

    public function batchSize(): int
    {
        return 5000;
    }

    public function chunkSize(): int
    {
        return 5000;
    }
}
