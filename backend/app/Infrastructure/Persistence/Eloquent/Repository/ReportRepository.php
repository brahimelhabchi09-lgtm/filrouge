<?php

namespace App\Infrastructure\Persistence\Eloquent\Repository;

use App\Domain\Report\Entity\Report;
use App\Domain\Report\Repository\ReportRepository as RepositoryReportRepository;
use App\Infrastructure\Persistence\Eloquent\Model\Report as ModelReport;

class ReportRepository implements RepositoryReportRepository
{
    public function save(Report $report): int
    {
        $reportObject = ModelReport::create([
            'title' => $report->getTitle(),
            'description' => $report->getDescription(),
            'student_id' => $report->getStudentId(),
            'category_id' => $report->getCategoryId(),
            'generated_report_id' => $report->getGeneratedReportId() ?? null,
        ]);

        return $reportObject->id;
    }

    public function associateReportWithGeneratedReport(int $reportId, int $generatedReportId): void
    {
        ModelReport::where('id', $reportId)->update(['generated_report_id' => $generatedReportId]);
    }
}
