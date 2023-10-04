<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use DB;
use App\Models\AdminProduct;

class ProcessCsvFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $filePath;

    /**
     * Create a new job instance.
     */
    public function __construct($filePath)
    {
        $this->filePath = $filePath;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $batchSize = 1000; // Adjust this based on your memory limits
        $header = null;

        if (($handle = fopen($this->filePath, 'r')) !== false) {
            $rowCount = 0;
            $batch = [];

            while (($data = fgetcsv($handle)) !== false) {
                if (!$header) {
                    $header = $data;
                } else {
                    $row = array_combine($header, $data);

                    // Set created_at and updated_at timestamps
                    $row['created_at'] = now();
                    $row['updated_at'] = now();                    
                    $batch[] = $row;

                    $rowCount++;

                    if ($rowCount % $batchSize === 0) {
                        // Process the current batch
                        $this->processBatch($batch);
                        $batch = []; // Clear the batch
                    }
                }
            }

            // Process any remaining rows in the last batch
            if (!empty($batch)) {
                $this->processBatch($batch);
            }

            fclose($handle);
        }
    }
    private function processBatch(array $batch): void
    {
        
        DB::table('admin_product_list')->upsert($batch, 'source_sku_id');

    }
}
