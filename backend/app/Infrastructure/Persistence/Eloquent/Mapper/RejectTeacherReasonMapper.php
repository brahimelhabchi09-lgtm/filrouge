<?php

namespace App\Infrastructure\Persistence\Eloquent\Mapper;

use App\Domain\RejectTeacherReason\Entity\RejectTeacherReason;
use App\Infrastructure\Persistence\Eloquent\Model\RejectTeacherReason as RejectTeacherReasonModel;
use DateTime;

class RejectTeacherReasonMapper
{
    
    public function toDomain(RejectTeacherReasonModel $model): RejectTeacherReason
    {
        return new RejectTeacherReason(
            message: $model->message,
            teacherId: $model->teacher_id,
            generatedReportId: $model->generated_report_id,
            id: $model->id,
            createdAt: new DateTime($model->created_at),
            updatedAt: new DateTime($model->updated_at)
        );
    }

  
    public function toEloquent(RejectTeacherReason $entity): RejectTeacherReasonModel
    {
        $model = new RejectTeacherReasonModel();
        
        if ($entity->getId() !== null) {
            $model->id = $entity->getId();
            $model->exists = true;
        }
        
        $model->message = $entity->getMessage();
        $model->teacher_id = $entity->getTeacherId();
        $model->generated_report_id = $entity->getGeneratedReportId();
        
        return $model;
    }

   
    public function updateEloquent(RejectTeacherReasonModel $model, RejectTeacherReason $entity): void
    {
        $model->message = $entity->getMessage();
        $model->teacher_id = $entity->getTeacherId();
        $model->generated_report_id = $entity->getGeneratedReportId();
    }

    
    public function toDomainCollection(iterable $models): array
    {
        $entities = [];
        foreach ($models as $model) {
            $entities[] = $this->toDomain($model);
        }
        return $entities;
    }
}