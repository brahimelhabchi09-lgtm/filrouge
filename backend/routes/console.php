<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('reject-reasons:clear', function () {
    $count = DB::table('rejected_teacher_reasons')->count();
    
    if ($this->confirm("Are you sure you want to delete {$count} reject teacher reasons?")) {
        DB::table('rejected_teacher_reasons')->truncate();
        $this->info('All reject teacher reasons have been deleted.');
    } else {
        $this->info('Operation cancelled.');
    }
})->purpose('Clear all reject teacher reasons from the database');

Artisan::command('reject-reasons:stats', function () {
    $total = DB::table('rejected_teacher_reasons')->count();
    $byTeacher = DB::table('rejected_teacher_reasons')
        ->select('teacher_id', DB::raw('count(*) as total'))
        ->groupBy('teacher_id')
        ->orderByDesc('total')
        ->limit(5)
        ->get();
    
    $this->info("Total reject teacher reasons: {$total}");
    $this->newLine();
    
    if ($byTeacher->isNotEmpty()) {
        $this->info('Top 5 teachers with most rejections:');
        $this->table(['Teacher ID', 'Total Rejections'], $byTeacher->map(fn($item) => [
            $item->teacher_id,
            $item->total
        ]));
    }
})->purpose('Display statistics about reject teacher reasons');
