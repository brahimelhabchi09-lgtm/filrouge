<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE generated_reports DROP CONSTRAINT IF EXISTS generated_reports_status_check");
            DB::statement("ALTER TABLE generated_reports ADD CONSTRAINT generated_reports_status_check CHECK (status IN ('pending', 'resolved', 'rejected', 'escalated'))");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE generated_reports DROP CONSTRAINT IF EXISTS generated_reports_status_check");
            DB::statement("ALTER TABLE generated_reports ADD CONSTRAINT generated_reports_status_check CHECK (status IN ('pending', 'resolved', 'rejected'))");
        }
    }
};
