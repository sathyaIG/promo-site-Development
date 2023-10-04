<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_upload_log', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('extract_status')->comment('0=> not yet start, 1 => on process, 2 => completed');
            $table->integer('upload_type')->nullable()->comment('1=>product_Upload, 2=>User_upload, 3=>Business_Type, 4=>Ad_Type, 5=>Keyword, 6=>Rate_Card_Type, 7=>Campaign_Box, 8=>campaign_upload, 12=>Bidding_Keywords,15=>Manufacturer,16=>Keyword In-Active Upload ,17=>Parent Asset Upload, 18=>Child Asset Upload, 19 => Campaign by Asset Upload, 20 => Free Campaign by Asset Upload');
            $table->string('file_name')->nullable();
            $table->string('file_orgname')->nullable();
            $table->string('file_path');
            $table->string('source_path')->nullable();
            $table->string('dest-path')->nullable();
            $table->string('product_excel')->nullable();
            $table->string('purity_excel')->nullable();
            $table->integer('purity_id')->nullable();
            $table->integer('status')->default(1);
            $table->integer('created_by')->nullable();
            $table->enum('trash', ['NO', 'YES'])->default('NO');
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('admin_upload_log');
    }
};
