<?php

namespace App\Mail;

use App\Model\Meeting;
use App\Model\RequestMeeting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;

class MeetingScheduledMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Meeting $meeting,
        public RequestMeeting $requestMeeting
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Meeting Scheduled: ' . $this->meeting->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.meeting-scheduled',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
