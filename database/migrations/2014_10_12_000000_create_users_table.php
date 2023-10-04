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
        Schema::create('users', function (Blueprint $table) {

            $table->id();
            $table->string('name', 100);
            $table->string('email', 100);
            $table->integer('role',false,true);
            $table->string('mobile', 20)->nullable();
            $table->string('profile_image')->nullable()->default(NULL);
            $table->integer('department',false,true)->nullable();
            $table->integer('business_type',false,true)->nullable();
            $table->boolean('is_active')->default('0');
            $table->string('active_tokan')->nullable()->default(NULL);
            $table->integer('created_by',false,true)->nullable();
            $table->date('created_date')->nullable();
            $table->integer('status',false,true)->default(1);
            $table->enum('trash', ['NO', 'YES'])->default('NO');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
