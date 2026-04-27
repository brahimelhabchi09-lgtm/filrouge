<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class GeneratedReport extends Model
{
    protected $table = 'generated_reports';

    protected $fillable = [
        'message',
        'priority',
        'status',
        'reports_count',
        'bde_reason',
    ];

    public function bde(): BelongsTo
    {
        return $this->belongsTo(User::class, 'bde_id');
    }

    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    public function requestMeeting(): HasOne
    {
        return $this->hasOne(RequestMeeting::class, 'generated_report_id');
    }
}
