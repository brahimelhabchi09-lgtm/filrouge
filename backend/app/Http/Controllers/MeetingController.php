<?php

namespace App\Http\Controllers;

use App\Infrastructure\Persistence\Eloquent\Model\GeneratedReport;
use App\Infrastructure\Persistence\Eloquent\Model\Meeting;
use App\Infrastructure\Persistence\Eloquent\Model\RequestMeeting;
use App\Mail\MeetingScheduledMail;
use App\Services\OpenAIPdfReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class MeetingController extends Controller
{
    private function getUserId(Request $request)
    {
        return $request->header('X-User-Id');
    }

    public function index(Request $request)
    {
        $meetings = Meeting::with(['admin', 'requestMeeting.requestMeeting.bde'])
            ->orderByDesc('date')
            ->paginate(20);

        return response()->json([
            'data' => $meetings->items(),
            'meta' => [
                'current_page' => $meetings->currentPage(),
                'per_page' => $meetings->perPage(),
                'total' => $meetings->total(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'request_meeting_id' => 'required|integer|exists:request_meetings,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date' => 'required|date|after:now',
            'link' => 'nullable|string|max:500',
        ]);

        $userId = $this->getUserId($request);

        $requestMeeting = RequestMeeting::with(['bde', 'generatedReport.reports.student', 'generatedReport.reports.category'])
            ->findOrFail($validated['request_meeting_id']);

        if ($requestMeeting->status !== RequestMeeting::STATUS_PENDING) {
            return response()->json([
                'message' => 'This meeting request has already been processed.',
            ], 422);
        }

        $requestMeeting->load(['bde', 'generatedReport']);
        
        $googleMeetLink = !empty($validated['link']) ? $validated['link'] : 
                          (!empty($requestMeeting->meeting_link) ? $requestMeeting->meeting_link : $this->generateGoogleMeetLink());

        $meeting = Meeting::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? '',
            'date' => $validated['date'],
            'link' => $googleMeetLink,
            'pdf_path' => null,
            'admin_id' => $userId ?? null,
            'request_meeting_id' => $validated['request_meeting_id'],
        ]);

        $requestMeeting->update([
            'status' => RequestMeeting::STATUS_APPROVED,
        ]);

        $generatedReport = $requestMeeting->generatedReport;
        if ($generatedReport) {
            $generatedReport->reports()->update(['status' => 'resolved']);
            $generatedReport->update(['status' => 'resolved']);
        }

        $reportService = new OpenAIPdfReportService();
        try {
            $pdfPath = $reportService->generatePdfReport($meeting, $requestMeeting);
            if ($pdfPath) {
                $meeting->update(['pdf_path' => $pdfPath]);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to generate PDF report: ' . $e->getMessage());
        }

        $this->sendMeetingEmail($meeting, $requestMeeting);

        $meeting->load(['admin', 'requestMeeting']);

        return response()->json([
            'message' => 'Meeting scheduled successfully.',
            'meeting' => $meeting->fresh(),
        ], 201);
    }

    private function generateGoogleMeetLink(): string
    {
        // Generate a link with the format: meet.google.com/xxx-xxxx-xxx
        $chars = 'abcdefghijklmnopqrstuvwxyz';
        $p1 = substr(str_shuffle($chars), 0, 3);
        $p2 = substr(str_shuffle($chars), 0, 4);
        $p3 = substr(str_shuffle($chars), 0, 3);
        
        return "https://meet.google.com/{$p1}-{$p2}-{$p3}";
    }

    private function sendMeetingEmail(Meeting $meeting, RequestMeeting $requestMeeting): void
    {
        $requestMeeting->load(['bde', 'generatedReport']);
        
        $bde = $requestMeeting->bde;
        
        if ($bde && $bde->email) {
            try {
                Mail::to($bde->email)->send(new MeetingScheduledMail($meeting, $requestMeeting));
                \Log::info('Meeting email sent to: ' . $bde->email . ' for meeting: ' . $meeting->title);
            } catch (\Exception $e) {
                \Log::error('Failed to send meeting email: ' . $e->getMessage());
                \Log::info('Meeting scheduled but email failed. Meeting link: ' . $meeting->link);
            }
        } else {
            \Log::info('Meeting scheduled but no BDE email found. Meeting link: ' . $meeting->link);
        }
    }

    public function show(Request $request, int $id)
    {
        $meeting = Meeting::with(['admin', 'requestMeeting.requestMeeting.bde', 'requestMeeting.generatedReport'])
            ->findOrFail($id);

        return response()->json([
            'meeting' => $meeting,
        ]);
    }

    public function reject(Request $request, int $id)
    {
        $validated = $request->validate([
            'reason' => 'required|string|min:10|max:1000',
        ]);

        $requestMeeting = RequestMeeting::findOrFail($id);

        if ($requestMeeting->status !== RequestMeeting::STATUS_PENDING) {
            return response()->json([
                'message' => 'This meeting request has already been processed.',
            ], 422);
        }

        $requestMeeting->update([
            'status' => RequestMeeting::STATUS_REJECTED,
            'rejection_reason' => $validated['reason'],
        ]);

        return response()->json([
            'message' => 'Meeting request rejected.',
            'request_meeting' => $requestMeeting->fresh(),
        ]);
    }
}
