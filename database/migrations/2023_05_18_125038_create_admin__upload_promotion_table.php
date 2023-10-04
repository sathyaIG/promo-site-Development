<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('admin_upload_promotion', function (Blueprint $table) {
            $table->id();
            $table->string('file_name');
            $table->integer('promo_type',false,true);
            $table->integer('manufacturer_id',false,true);
            $table->string('file_path');
            $table->string('business_type_id');
            $table->string('theme_id');
            $table->string('region_id');
            $table->integer('promo_details_status',false,true)->default(0);
            $table->enum('status', ['0', '1']);
            $table->string('created_by');
            $table->date('created_date')->nullable();
            $table->string('updated_by')->nullable()->default('NULL');
            $table->enum('trash', ['NO', 'YES'])->default('NO');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_upload_promotion');
    }
};
