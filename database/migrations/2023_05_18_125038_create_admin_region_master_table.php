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
        Schema::create('admin_region_master', function (Blueprint $table) {
            $table->id();
            $table->string('region');
            $table->string('state');
            $table->integer('city',false,true);
            $table->enum('status', ['0', '1']);
            $table->string('created_by');
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
        Schema::dropIfExists('admin_region_master');
    }
};
