<?php

namespace App\Http\Controllers;

use App\Infrastructure\Persistence\Eloquent\Model\Report;
use App\Infrastructure\Persistence\Eloquent\Model\RejectTeacherReason;
use App\Infrastructure\Persistence\Eloquent\Model\GeneratedReport;
use App\Infrastructure\Persistence\Eloquent\Model\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeacherController extends Controller
{
    private const PEDAGOGY_CATEGORIES = [2, 4, 5];

    private function getUserId()
    {
        return Auth::id();
    }

    public function dashboard(Request $request)
    {
        $userId = $this->getUserId();
        
        $rejectedReasons = RejectTeacherReason::with(['teacher', 'generatedReport'])
            ->where('teacher_id', $userId)
            ->get();

        $pendingReports = Report::whereHas('category', function ($q) {
            $q->whereIn('id', self::PEDAGOGY_CATEGORIES);
        })
        ->whereHas('generatedReport', function ($q) {
            $q->where('status', 'pending');
        })
        ->with(['student', 'category', 'generatedReport'])
        ->get();

        return response()->json([
            'rejectedReasons' => $rejectedReasons,
            'pendingReports' => $pendingReports,
        ]);
    }

    public function reports()
    {
        $reports = Report::whereHas('category', function ($q) {
            $q->whereIn('id', self::PEDAGOGY_CATEGORIES);
        })
        ->with(['student', 'category', 'generatedReport'])
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

    public function users()
    {
        $users = User::where('role', 'STUDENT')
            ->orderBy('first_name')
            ->get();

        return response()->json([
            'users' => $users
        ]);
    }

    public function resolve(Request $request, int $report)
    {
        $reportModel = Report::with('generatedReport')->findOrFail($report);

        $reportModel->update(['status' => 'resolved']);

        $generatedReport = $reportModel->generatedReport;
        if ($generatedReport) {
            $allResolved = $generatedReport->reports()->where('status', '!=', 'resolved')->count() === 0;
            if ($allResolved) {
                $generatedReport->update(['status' => 'resolved']);
            }
        }

        return response()->json([
            'message' => 'Report marked as resolved.',
            'report' => $reportModel->fresh(),
        ]);
    }

    public function reject(Request $request, int $report)
    {
        $validated = $request->validate([
            'reason' => 'required|string|min:10|max:1000',
        ]);

        $userId = $this->getUserId();
        $reportModel = Report::with('generatedReport')->findOrFail($report);

        RejectTeacherReason::create([
            'generated_report_id' => $reportModel->generated_report_id,
            'teacher_id' => $userId,
            'message' => $validated['reason'],
        ]);

        $reportModel->update(['status' => 'rejected']);

        return response()->json([
            'message' => 'Report rejected.',
            'report' => $reportModel->fresh(),
        ]);
    }
}
