<?php

namespace App\Http\Controllers;

use App\Infrastructure\Persistence\Eloquent\Model\Category;
use App\Infrastructure\Persistence\Eloquent\Model\Report;
use App\Infrastructure\Persistence\Eloquent\Model\GeneratedReport;
use App\Infrastructure\Persistence\Eloquent\Model\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentController extends Controller
{
    private function getUserId()
    {
        return Auth::id();
    }

    public function dashboard(Request $request)
    {
        $userId = $this->getUserId();
        
        $stats = [
            'total' => Report::where('student_id', $userId)->count(),
            'pending' => Report::where('student_id', $userId)->where('status', 'pending')->count(),
            'resolved' => Report::where('student_id', $userId)->where('status', 'resolved')->count(),
            'rejected' => Report::where('student_id', $userId)->where('status', 'rejected')->count(),
        ];

        return response()->json([
            'message' => 'Student Dashboard',
            'stats' => $stats,
        ]);
    }

    public function myReports(Request $request)
    {
        $userId = $this->getUserId();
        
        $reports = Report::where('student_id', $userId)
            ->with(['category', 'generatedReport'])
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

    public function createReportForm()
    {
        $categories = Category::all();
        return response()->json(['categories' => $categories]);
    }

    public function storeReport(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|min:10',
            'category_id' => 'required|integer|exists:categories,id',
        ]);

        $userId = $this->getUserId();

        $generatedReport = GeneratedReport::create([
            'message' => $validated['description'],
            'priority' => 'P2',
            'status' => 'pending',
            'reports_count' => 1,
        ]);

        $report = Report::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'student_id' => $userId,
            'category_id' => $validated['category_id'],
            'generated_report_id' => $generatedReport->id,
            'status' => 'pending',
        ]);

        $report->load(['category', 'generatedReport']);

        return response()->json([
            'message' => 'Report submitted successfully!',
            'report' => $report,
        ], 201);
    }
}
