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
            $table->enum('status', ['pending', 'approved', 'rejected', 'resolved'])->default('pending');
            $table->text('bde_reason')->nullable();
            $table->unsignedBigInteger('bde_id')->nullable();
            $table->foreign('bde_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('generated_reports', function (Blueprint $table) {
            $table->dropForeign(['bde_id']);
            $table->dropColumn(['status', 'bde_reason', 'bde_id']);
        });
    }
};
