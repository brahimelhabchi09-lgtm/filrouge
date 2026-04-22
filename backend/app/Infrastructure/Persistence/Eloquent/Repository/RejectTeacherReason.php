<?php

namespace App\Infrastructure\Persistence\Eloquent\Repository;

use App\Domain\RejectTeacherReason\Entity\RejectTeacherReason as RejectTeacherReasonEntity;
use App\Domain\RejectTeacherReason\Repository\RejectTeacherReasonRepository as RejectTeacherReasonRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Mapper\RejectTeacherReasonMapper;
use App\Infrastructure\Persistence\Eloquent\Model\RejectTeacherReason as RejectTeacherReasonModel;

class RejectTeacherReasonRepository implements RejectTeacherReasonRepositoryInterface
{
    public function __construct(
        private RejectTeacherReasonMapper $mapper
    ) {
    }

    
    public function save(RejectTeacherReasonEntity $reason): RejectTeacherReasonEntity
    {
        if ($reason->getId() !== null) {
            $model = RejectTeacherReasonModel::findOrFail($reason->getId());
            $this->mapper->updateEloquent($model, $reason);
        } else {
            $model = $this->mapper->toEloquent($reason);
        }

        $model->save();

        return $this->mapper->toDomain($model);
    }

    
    public function findById(int $id): ?RejectTeacherReasonEntity
    {
        $model = RejectTeacherReasonModel::find($id);

        return $model ? $this->mapper->toDomain($model) : null;
    }

    
    public function findByTeacherId(int $teacherId): array
    {
        $models = RejectTeacherReasonModel::where('teacher_id', $teacherId)->get();

        return $this->mapper->toDomainCollection($models);
    }

    
    public function findByGeneratedReportId(int $generatedReportId): array
    {
        $models = RejectTeacherReasonModel::where('generated_report_id', $generatedReportId)->get();

        return $this->mapper->toDomainCollection($models);
    }

    public function delete(int $id): bool
    {
        $model = RejectTeacherReasonModel::find($id);

        if ($model === null) {
            return false;
        }

        return $model->delete();
    }

    
    public function findAll(): array
    {
        $models = RejectTeacherReasonModel::all();

        return $this->mapper->toDomainCollection($models);
    }
}
