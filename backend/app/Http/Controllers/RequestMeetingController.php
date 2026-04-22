<?php

namespace App\Http\Controllers;

use App\Infrastructure\Persistence\Eloquent\Model\GeneratedReport;
use App\Infrastructure\Persistence\Eloquent\Model\RequestMeeting;
use App\Infrastructure\Persistence\Eloquent\Model\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RequestMeetingController extends Controller
{
    private function getUserId(Request $request)
    {
        return $request->header('X-User-Id');
    }

    private function getUser(Request $request)
    {
        $userId = $this->getUserId($request);
        if ($userId) {
            return User::find($userId);
        }
        return Auth::user();
    }

    public function index(Request $request)
    {
        $user = $this->getUser($request);
        
        if (!$user) {
            return response()->json(['message' => 'User not found'], 401);
        }
        
        $role = strtoupper((string) $user->role);

        $query = RequestMeeting::query()
            ->with(['bde', 'generatedReport', 'meeting'])
            ->orderByDesc('created_at');

        if ($role === 'BDE') {
            $query->where('bde_id', $user->id);
        } elseif ($role === 'TEACHER') {
            $query->whereHas('generatedReport', function ($q) use ($user) {
                $q->whereHas('reports', function ($q2) {
                    $q2->whereIn('category_id', [2, 4, 5]);
                });
            });
        }

        $requestMeetings = $query->paginate(20);

        return response()->json([
            'data' => $requestMeetings->items(),
            'meta' => [
                'current_page' => $requestMeetings->currentPage(),
                'per_page' => $requestMeetings->perPage(),
                'total' => $requestMeetings->total(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'generated_report_id' => 'required|integer|exists:generated_reports,id',
            'meeting_date' => 'required|date|after:now',
            'notes' => 'nullable|string|max:1000',
        ]);

        $userId = $this->getUserId($request);
        
        if (!$userId) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }

        $existingRequest = RequestMeeting::where('generated_report_id', $validated['generated_report_id'])
            ->where('status', RequestMeeting::STATUS_PENDING)
            ->first();

        if ($existingRequest) {
            return response()->json([
                'message' => 'A meeting request already exists for this report.',
            ], 422);
        }

        $requestMeeting = RequestMeeting::create([
            'bde_id' => $userId,
            'generated_report_id' => $validated['generated_report_id'],
            'meeting_date' => $validated['meeting_date'],
            'notes' => $validated['notes'] ?? null,
            'status' => RequestMeeting::STATUS_PENDING,
        ]);

        $requestMeeting->load(['bde', 'generatedReport']);

        return response()->json([
            'message' => 'Meeting request submitted successfully.',
            'request_meeting' => $requestMeeting,
        ], 201);
    }

    public function show(Request $request, int $id)
    {
        $requestMeeting = RequestMeeting::with(['bde', 'generatedReport', 'generatedReport.reports.category', 'meeting'])
            ->findOrFail($id);

        return response()->json([
            'request_meeting' => $requestMeeting,
        ]);
    }
}
