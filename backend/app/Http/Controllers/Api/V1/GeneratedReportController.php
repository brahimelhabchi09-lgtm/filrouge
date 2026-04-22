<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreGeneratedReportRequest;
use App\Http\Resources\Api\V1\GeneratedReportResource;
use App\Http\Resources\Api\V1\ReportResource;
use App\Infrastructure\Persistence\Eloquent\Model\GeneratedReport;
use App\Infrastructure\Persistence\Eloquent\Model\Report;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class GeneratedReportController extends Controller
{
    /**
     * GET /api/v1/generated-reports
     */
    public function index(): JsonResponse
    {
        $user = Auth::user();
        $isStudent = strtoupper((string) $user->role) === 'STUDENT';

        $query = GeneratedReport::query()
            ->whereHas('reports', function ($q) use ($user, $isStudent) {
                if ($isStudent) {
                    $q->where('student_id', $user->id);
                }
            })
            ->with([
                'reports' => function ($q) use ($user, $isStudent) {
                    if ($isStudent) {
                        $q->where('student_id', $user->id);
                    }
                    $q->with('category');
                },
            ])
            ->orderByDesc('created_at');

        $generatedReports = $query->paginate(20);

        return response()->json([
            'data' => GeneratedReportResource::collection($generatedReports),
            'meta' => [
                'current_page' => $generatedReports->currentPage(),
                'per_page' => $generatedReports->perPage(),
                'total' => $generatedReports->total(),
            ],
        ]);
    }

    /**
     * POST /api/v1/generated-reports
     * Only ADMIN can create.
     */
    public function store(StoreGeneratedReportRequest $request): JsonResponse
    {
        $user = Auth::user();
        $validated = $request->validated();

        $reportIds = $validated['report_ids'];

        return DB::transaction(function () use ($reportIds, $user) {
            /** @var Report $firstReport */
            $firstReport = Report::query()
                ->whereIn('id', $reportIds)
                ->orderBy('id')
                ->first();

            // Validation already ensures existence; this is just a safety net.
            if (! $firstReport) {
                return response()->json([
                    'message' => 'No valid reports found.',
                ], 422);
            }

            $generatedReport = GeneratedReport::create([
                // This column is NOT NULL in your schema; use the first report as a placeholder.
                'message' => $firstReport->description,
                'priority' => 'P0',
                'status' => 'pending',
                'reports_count' => count($reportIds),
            ]);

            Report::query()
                ->whereIn('id', $reportIds)
                ->update(['generated_report_id' => $generatedReport->id]);

            $generatedReport->load([
                'reports' => function ($q) {
                    $q->with('category');
                },
            ]);

            return (new GeneratedReportResource($generatedReport))
                ->response()
                ->setStatusCode(Response::HTTP_CREATED);
        });
    }

    /**
     * GET /api/v1/generated-reports/{id}
     */
    public function show(GeneratedReport $generatedReport): JsonResponse
    {
        $user = Auth::user();
        $isStudent = strtoupper((string) $user->role) === 'STUDENT';

        $query = GeneratedReport::query()
            ->with([
                'reports' => function ($q) use ($user, $isStudent) {
                    if ($isStudent) {
                        $q->where('student_id', $user->id);
                    }
                    $q->with('category');
                },
            ])
            ->where('id', $generatedReport->id);

        if ($isStudent) {
            $query->whereHas('reports', function ($q) use ($user) {
                $q->where('student_id', $user->id);
            });
        }

        $model = $query->first();

        if (! $model) {
            return response()->json([
                'message' => 'Generated report not found.',
            ], 404);
        }

        return (new GeneratedReportResource($model))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }
}

