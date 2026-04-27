<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReportRequest;
use App\Http\Resources\ReportResource;
use App\Model\GeneratedReport;
use App\Model\Report;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    /**
     * GET /api/v1/reports
     */
    public function index(): JsonResponse
    {
        $user = Auth::user();

        $query = Report::query()
            ->with(['category', 'generatedReport'])
            ->orderByDesc('created_at');

        // RBAC: STUDENT sees their own reports only.
        if (strtoupper((string) $user->role) === 'STUDENT') {
            $query->where('student_id', $user->id);
        }

        $reports = $query->paginate(20);

        return response()->json([
            'data' => ReportResource::collection($reports),
            'meta' => [
                'current_page' => $reports->currentPage(),
                'per_page' => $reports->perPage(),
                'total' => $reports->total(),
            ],
        ]);
    }

    /**
     * POST /api/v1/reports
     */
    public function store(StoreReportRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        // Group into existing pending GeneratedReport for same category, escalate priority
        $generated = GeneratedReport::whereHas('reports', function ($q) use ($validated) {
                $q->where('category_id', (int) $validated['category_id']);
            })
            ->whereIn('status', ['pending'])
            ->first();

        if ($generated) {
            $newCount = $generated->reports_count + 1;
            $generated->update([
                'reports_count' => $newCount,
                'priority'      => $this->escalatePriority($newCount),
            ]);
        } else {
            $generated = GeneratedReport::create([
                'message'       => $validated['description'],
                'priority'      => 'P3',
                'status'        => 'pending',
                'reports_count' => 1,
            ]);
        }

        $report = Report::create([
            'title'               => $validated['title'],
            'description'         => $validated['description'],
            'student_id'          => $user->id,
            'category_id'         => (int) $validated['category_id'],
            'generated_report_id' => $generated->id,
        ]);

        $report->load(['category', 'generatedReport']);

        return (new ReportResource($report))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    private function escalatePriority(int $count): string
    {
        return match (true) {
            $count >= 4 => 'P0',
            $count === 3 => 'P1',
            $count === 2 => 'P2',
            default      => 'P3',
        };
    }

    /**
     * GET /api/v1/reports/{report}
     */
    public function show(int $report): JsonResponse
    {
        $user = Auth::user();

        $query = Report::query()
            ->with(['category', 'generatedReport'])
            ->where('id', $report);

        if (strtoupper((string) $user->role) === 'STUDENT') {
            $query->where('student_id', $user->id);
        }

        $reportModel = $query->first();

        if (! $reportModel) {
            return response()->json([
                'message' => 'Report not found.',
            ], 404);
        }

        return (new ReportResource($reportModel))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }
}

