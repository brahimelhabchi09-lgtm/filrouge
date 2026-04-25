<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE generated_reports DROP CONSTRAINT IF EXISTS generated_reports_priority_check");
            DB::statement("ALTER TABLE generated_reports ADD CONSTRAINT generated_reports_priority_check CHECK (priority IN ('P0','P1','P2','P3'))");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE generated_reports DROP CONSTRAINT IF EXISTS generated_reports_priority_check");
            DB::statement("ALTER TABLE generated_reports ADD CONSTRAINT generated_reports_priority_check CHECK (priority IN ('P0','P1','P2'))");
        }
    }
};
