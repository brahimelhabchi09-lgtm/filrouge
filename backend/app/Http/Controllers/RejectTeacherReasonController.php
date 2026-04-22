<?php

namespace App\Http\Controllers;

use App\Domain\RejectTeacherReason\Entity\RejectTeacherReason;
use App\Domain\RejectTeacherReason\Repository\RejectTeacherReasonRepository;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class RejectTeacherReasonController extends Controller
{
    public function __construct(
        private RejectTeacherReasonRepository $repository
    ) {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $reasons = $this->repository->findAll();
        return response()->json(['reasons' => $reasons]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required|string',
            'teacher_id' => 'required|integer|exists:users,id',
            'generated_report_id' => 'required|integer|exists:generated_reports,id',
        ]);

        $reason = new RejectTeacherReason(
            message: $validated['message'],
            teacherId: $validated['teacher_id'],
            generatedReportId: $validated['generated_report_id']
        );

        $this->repository->save($reason);

        return response()->json([
            'message' => 'Reject reason created successfully.',
            'reason' => [
                'message' => $reason->getMessage(),
                'teacherId' => $reason->getTeacherId(),
                'generatedReportId' => $reason->getGeneratedReportId()
            ]
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): JsonResponse
    {
        $reason = $this->repository->findById($id);

        if ($reason === null) {
            return response()->json(['message' => 'Reject reason not found'], 404);
        }

        return response()->json(['reason' => [
            'id' => $reason->getId(),
            'message' => $reason->getMessage(),
            'teacherId' => $reason->getTeacherId(),
            'generatedReportId' => $reason->getGeneratedReportId()
        ]]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required|string',
        ]);

        $reason = $this->repository->findById($id);

        if ($reason === null) {
            return response()->json(['message' => 'Reject reason not found'], 404);
        }

        $reason->setMessage($validated['message']);
        $this->repository->save($reason);

        return response()->json([
            'message' => 'Reject reason updated successfully.',
            'reason' => [
                'id' => $reason->getId(),
                'message' => $reason->getMessage(),
                'teacherId' => $reason->getTeacherId(),
                'generatedReportId' => $reason->getGeneratedReportId()
            ]
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->repository->delete($id);

        if (!$deleted) {
            return response()->json(['message' => 'Reject reason not found.'], 404);
        }

        return response()->json(['message' => 'Reject reason deleted successfully.']);
    }

    /**
     * Get reject reasons by teacher ID (API endpoint).
     */
    public function byTeacher(int $teacherId): JsonResponse
    {
        $reasons = $this->repository->findByTeacherId($teacherId);

        return response()->json($reasons);
    }

    /**
     * Get reject reasons by generated report ID (API endpoint).
     */
    public function byGeneratedReport(int $generatedReportId): JsonResponse
    {
        $reasons = $this->repository->findByGeneratedReportId($generatedReportId);

        return response()->json($reasons);
    }
}
