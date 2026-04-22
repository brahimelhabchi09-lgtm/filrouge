<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('request_meetings', function (Blueprint $table) {
            $table->string('status')->default('pending')->after('generated_report_id');
            $table->dateTime('meeting_date')->nullable()->after('status');
            $table->text('notes')->nullable()->after('meeting_date');
            $table->text('rejection_reason')->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('request_meetings', function (Blueprint $table) {
            $table->dropColumn(['status', 'meeting_date', 'notes', 'rejection_reason']);
        });
    }
};
