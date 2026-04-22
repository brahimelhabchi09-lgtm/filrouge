<?php

namespace App\Domain\RejectTeacherReason\Repository;

use App\Domain\RejectTeacherReason\Entity\RejectTeacherReason;

interface RejectTeacherReasonRepository
{
    
    public function save(RejectTeacherReason $reason): RejectTeacherReason;

    
    public function findById(int $id): ?RejectTeacherReason;

    public function findByTeacherId(int $teacherId): array;

    
    public function findByGeneratedReportId(int $generatedReportId): array;

    
    public function delete(int $id): bool;

    public function findAll(): array;
}