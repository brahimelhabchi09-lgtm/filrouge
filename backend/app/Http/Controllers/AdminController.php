<?php

namespace App\Http\Controllers;

use App\Infrastructure\Persistence\Eloquent\Model\User;
use App\Infrastructure\Persistence\Eloquent\Model\RequestMeeting;
use App\Infrastructure\Persistence\Eloquent\Model\Meeting;
use App\Infrastructure\Persistence\Eloquent\Model\Report;
use App\Infrastructure\Persistence\Eloquent\Model\GeneratedReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 10);
        $users = User::orderByDesc('created_at')->paginate($perPage);
        
        $stats = [
            'total_users' => User::count(),
            'total_reports' => Report::count(),
            'pending_reports' => Report::where('status', 'pending')->count(),
            'resolved_reports' => Report::where('status', 'resolved')->count(),
        ];

        return response()->json([
            'users' => $users->items(),
            'meta' => [
                'current_page' => $users->currentPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
                'last_page' => $users->lastPage(),
            ],
            'stats' => $stats
        ]);
    }

    public function reports(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 10);
        $reports = Report::with(['student', 'category', 'generatedReport'])
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return response()->json([
            'data' => $reports->items(),
            'meta' => [
                'current_page' => $reports->currentPage(),
                'per_page' => $reports->perPage(),
                'total' => $reports->total(),
                'last_page' => $reports->lastPage(),
            ],
        ]);
    }

    public function meetings(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 10);
        $meetings = Meeting::with(['admin', 'requestMeeting'])
            ->orderByDesc('date')
            ->paginate($perPage);

        return response()->json([
            'data' => $meetings->items(),
            'meta' => [
                'current_page' => $meetings->currentPage(),
                'per_page' => $meetings->perPage(),
                'total' => $meetings->total(),
                'last_page' => $meetings->lastPage(),
            ],
        ]);
    }

    public function requestMeetings(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 10);
        $requestMeetings = RequestMeeting::with(['bde', 'generatedReport'])
            ->where('status', 'pending')
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return response()->json([
            'data' => $requestMeetings->items(),
            'meta' => [
                'current_page' => $requestMeetings->currentPage(),
                'per_page' => $requestMeetings->perPage(),
                'total' => $requestMeetings->total(),
                'last_page' => $requestMeetings->lastPage(),
            ],
        ]);
    }
}
