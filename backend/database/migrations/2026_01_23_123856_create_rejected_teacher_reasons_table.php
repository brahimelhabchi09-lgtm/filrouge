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
        Schema::create('rejected_teacher_reasons', function (Blueprint $table) {
            $table->id();
            $table->text('message');
            $table->unsignedBigInteger('teacher_id');
            $table->unsignedBigInteger('generated_report_id');
            
            // Foreign key constraints
            $table->foreign('teacher_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('generated_report_id')->references('id')->on('generated_reports')->onDelete('cascade');
            
            // Indexes for better query performance
            $table->index('teacher_id');
            $table->index('generated_report_id');
            
            $table->timestamps();
        });
    }
    
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rejected_teacher_reasons');
    }
};
