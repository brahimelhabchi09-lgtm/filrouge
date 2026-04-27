<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Meeting extends Model
{
    protected $table = 'meetings';

    protected $fillable = [
        'title',
        'description',
        'date',
        'link',
        'pdf_path',
        'admin_id',
        'request_meeting_id',
    ];

    protected $casts = [
        'date' => 'datetime',
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function requestMeeting(): BelongsTo
    {
        return $this->belongsTo(RequestMeeting::class, 'request_meeting_id');
    }
}
