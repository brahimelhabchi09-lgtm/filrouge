<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class RequestMeeting extends Model
{
    protected $table = 'request_meetings';

    protected $fillable = [
        'bde_id',
        'generated_report_id',
        'meeting_date',
        'notes',
        'meeting_link',
        'status',
        'rejection_reason',
    ];

    protected $casts = [
        'meeting_date' => 'datetime',
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    public function bde(): BelongsTo
    {
        return $this->belongsTo(User::class, 'bde_id');
    }

    public function generatedReport(): BelongsTo
    {
        return $this->belongsTo(GeneratedReport::class);
    }

    public function meeting(): HasOne
    {
        return $this->hasOne(Meeting::class, 'request_meeting_id');
    }
}
