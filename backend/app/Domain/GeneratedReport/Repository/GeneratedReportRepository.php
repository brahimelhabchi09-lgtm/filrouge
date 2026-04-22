<?php

namespace App\Domain\GeneratedReport\Repository;

use App\Domain\Report\Entity\GeneratedReport;

interface GeneratedReportRepository
{
    public function all(): array;
    public function save(GeneratedReport $report): int;

    public function find(int $id): ?GeneratedReport;

    public function updateCount(int $id, int $count): void;

    public function updatePriority(int $id, string $priority): void;
}
