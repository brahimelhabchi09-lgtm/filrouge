<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RejectTeacherReason extends Model
{
    
    protected $table = 'rejected_teacher_reasons';

    
    protected $fillable = [
        'message',
        'teacher_id',
        'generated_report_id',
    ];

    
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function generatedReport(): BelongsTo
    {
        return $this->belongsTo(GeneratedReport::class, 'generated_report_id');
    }
}
