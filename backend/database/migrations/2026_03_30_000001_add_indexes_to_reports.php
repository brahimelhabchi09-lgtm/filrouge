<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->index(['student_id']);
            $table->index(['category_id']);
            $table->index(['generated_report_id']);
        });

        Schema::table('generated_reports', function (Blueprint $table) {
            $table->index(['priority']);
            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropIndex(['student_id']);
            $table->dropIndex(['category_id']);
            $table->dropIndex(['generated_report_id']);
        });

        Schema::table('generated_reports', function (Blueprint $table) {
            $table->dropIndex(['priority']);
            $table->dropIndex(['status']);
        });
    }
};

