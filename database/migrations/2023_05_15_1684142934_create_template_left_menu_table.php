<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTemplateLeftMenuTable extends Migration
{
    public function up()
    {
        Schema::create('template_left_menu', function (Blueprint $table) {

		$table->bigIncrements('id');
		$table->string('name',255)->nullable()->default(NULL);
		$table->string('namekey',255)->nullable()->default(NULL);
		$table->string('link',255)->nullable()->default(NULL);
		$table->string('icon',255)->nullable()->default(NULL);
		$table->integer('parent_id',);
		$table->integer('is_parent',);
		$table->enum('is_module',['0','1']);
		$table->string('modkey',255)->nullable()->default(NULL);
		$table->integer('sort_order',);
		$table->integer('status',)->default(1);
		$table->enum('trash',['NO','YES'])->default('NO');
		$table->datetime('created_at')->default(\DB::raw('CURRENT_TIMESTAMP'));
		$table->datetime('updated_at')->default(\DB::raw('CURRENT_TIMESTAMP'))->useCurrent();

        });
    }

    public function down()
    {
        Schema::dropIfExists('template_left_menu');
    }
}
