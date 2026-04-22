<?php

namespace App\Http\Controllers;

use App\Infrastructure\Persistence\Eloquent\Model\GeneratedReport;
use App\Infrastructure\Persistence\Eloquent\Model\RequestMeeting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BDEController extends Controller
{
    private const BDE_CATEGORIES = [1, 3];

    private function getUserId()
    {
        return Auth::id();
    }

    public function dashboard(Request $request)
    {
        $userId = $this->getUserId();
        
        $pendingReports = GeneratedReport::where('status', 'pending')
            ->whereHas('reports', function ($query) {
                $query->whereIn('category_id', self::BDE_CATEGORIES);
            })
            ->with(['reports' => function ($query) {
                $query->with(['student', 'category']);
            }])
            ->orderByDesc('created_at')
            ->get();

        $requestMeetings = RequestMeeting::where('bde_id', $userId)
            ->with(['generatedReport', 'meeting'])
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'pendingReports' => $pendingReports,
            'requestMeetings' => $requestMeetings,
        ]);
    }

    public function reports()
    {
        $reports = GeneratedReport::with([
            'reports' => function ($query) {
                $query->with(['student', 'category']);
            },
            'requestMeeting'
        ])
        ->whereHas('reports', function ($query) {
            $query->whereIn('category_id', self::BDE_CATEGORIES);
        })
        ->orderByDesc('created_at')
        ->paginate(20);

        return response()->json([
            'data' => $reports->items(),
            'meta' => [
                'current_page' => $reports->currentPage(),
                'per_page' => $reports->perPage(),
                'total' => $reports->total(),
            ],
        ]);
    }

    public function approve(Request $request, $id)
    {
        $validated = $request->validate([
            'meeting_date' => 'required|date|after:now',
            'notes' => 'nullable|string|max:1000',
            'meeting_link' => 'nullable|string|max:500',
        ]);

        $userId = $this->getUserId();
        $generatedReport = GeneratedReport::findOrFail($id);
        
        $existingRequest = RequestMeeting::where('generated_report_id', $id)
            ->where('status', RequestMeeting::STATUS_PENDING)
            ->first();

        if ($existingRequest) {
            return response()->json([
                'message' => 'A meeting request already exists for this report.',
            ], 422);
        }

        $requestMeeting = RequestMeeting::create([
            'bde_id' => $userId,
            'generated_report_id' => $id,
            'meeting_date' => $validated['meeting_date'],
            'notes' => $validated['notes'] ?? null,
            'meeting_link' => $validated['meeting_link'] ?? null,
            'status' => RequestMeeting::STATUS_PENDING,
        ]);

        $generatedReport->update(['status' => 'escalated']);

        return response()->json([
            'message' => 'Meeting request submitted to admin.',
            'request_meeting' => $requestMeeting,
        ]);
    }

    public function deny(Request $request, $id)
    {
        $validated = $request->validate([
            'reason' => 'required|string|min:10',
        ]);

        $generatedReport = GeneratedReport::findOrFail($id);
        $generatedReport->update([
            'status' => 'rejected',
            'bde_reason' => $validated['reason'],
        ]);

        return response()->json([
            'message' => 'Report denied.',
            'generatedReport' => $generatedReport->fresh(),
        ]);
    }
}
