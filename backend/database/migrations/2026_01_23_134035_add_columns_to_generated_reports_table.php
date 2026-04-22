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
        Schema::table('generated_reports', function (Blueprint $table) {
            $table->text('message');
            $table->enum('priority', ['P0', 'P1', 'P2'])->default('P0');
            // status column already added in 2026_01_23_125000_add_status_to_generated_reports
            $table->integer('reports_count')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('generated_reports', function (Blueprint $table) {
            $table->dropColumn('message');
            $table->dropColumn('priority');
            // status column is handled in 2026_01_23_125000_add_status_to_generated_reports
            $table->dropColumn('reports_count');
        });
    }
};
