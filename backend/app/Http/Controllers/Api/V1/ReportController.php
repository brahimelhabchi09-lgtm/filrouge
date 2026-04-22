<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreReportRequest;
use App\Http\Resources\Api\V1\ReportResource;
use App\Infrastructure\Persistence\Eloquent\Model\GeneratedReport;
use App\Infrastructure\Persistence\Eloquent\Model\Report;
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

        // MVP approach:
        // - create a new "generated_report" grouping record per submission (no AI dedup yet)
        // - attach it to the created report via generated_report_id
        $generated = GeneratedReport::create([
            'message' => $validated['description'],
            'priority' => 'P0',
            'status' => 'pending',
            'reports_count' => 1,
        ]);

        $report = Report::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'student_id' => $user->id,
            'category_id' => (int) $validated['category_id'],
            'generated_report_id' => $generated->id,
        ]);

        $report->load(['category', 'generatedReport']);

        return (new ReportResource($report))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
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

