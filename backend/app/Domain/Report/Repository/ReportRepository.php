<?php

namespace App\Domain\Report\Repository;

use App\Domain\Report\Entity\Report;

interface ReportRepository
{
    public function save(Report $report): int;

    public function associateReportWithGeneratedReport(int $reportId, int $generatedReportId): void;
}
