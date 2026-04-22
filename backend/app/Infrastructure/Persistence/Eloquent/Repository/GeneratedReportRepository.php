<?php

namespace App\Infrastructure\Persistence\Eloquent\Repository;

use App\Domain\GeneratedReport\Repository\GeneratedReportRepository as RepositoryGeneratedReportRepository;
use App\Domain\Report\Entity\GeneratedReport as EntityGeneratedReport;
use App\Infrastructure\Persistence\Eloquent\Model\GeneratedReport;

class GeneratedReportRepository implements RepositoryGeneratedReportRepository
{
    public function all(): array
    {
        return GeneratedReport::all()->toArray();
    }

    public function save(EntityGeneratedReport $report): int
    {
        $generatedReport = GeneratedReport::create([
            'message' => $report->getMessage(),
            'priority' => $report->getPriority(),
            'status' => $report->getStatus(),
            'reports_count' => $report->getReportsCount(),
        ]);

        return $generatedReport->id;
    }

    public function find(int $id): ?EntityGeneratedReport
    {
        $generatedReport = GeneratedReport::find($id);

        return $generatedReport ? new EntityGeneratedReport(
            $generatedReport->message,
            $generatedReport->priority,
            $generatedReport->status,
            $generatedReport->reports_count,
            $generatedReport->id
        ) : null;
    }

    public function updateCount(int $id, int $count): void
    {
        GeneratedReport::where('id', $id)->update(['reports_count' => $count]);
    }

    public function updatePriority(int $id, string $priority): void
    {
        GeneratedReport::where('id', $id)->update(['priority' => $priority]);
    }
}
