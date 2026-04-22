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
        Schema::create('request_meetings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bde_id');
            $table->unsignedBigInteger('generated_report_id');
            $table->foreign('bde_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('generated_report_id')->references('id')->on('generated_reports')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_meetings');
    }
};
