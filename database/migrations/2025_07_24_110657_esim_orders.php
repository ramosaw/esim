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
        Schema::create('esim_orders', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('api_sold_id')->nullable();
    $table->string('gsm_no');
    $table->string('email');
    $table->string('tc_kimlik_no');
    $table->string('ad');
    $table->string('soyad');
    $table->date('dogum_tarihi');
    $table->string('paket_title');
    $table->string('fiyat');
    $table->string('data_amount')->nullable();
    $table->string('validity_period')->nullable();
    $table->string('status')->default('pending');
    $table->string('qr_code_url')->nullable();
    $table->string('coverage')->nullable();
    $table->string('iccid')->nullable();
    $table->date('start_date')->nullable();
    $table->date('end_date')->nullable();
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
