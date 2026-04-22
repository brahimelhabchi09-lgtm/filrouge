<?php

namespace App\Http\Resources\Api\V1;

use App\Infrastructure\Persistence\Eloquent\Model\Report;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Report
 */
class ReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'student_id' => $this->student_id,
            'category' => $this->whenLoaded('category', function () {
                return [
                    'id' => $this->category->id,
                    'name' => $this->category->name,
                    'description' => $this->category->description,
                ];
            }),
            'generated_report' => $this->whenLoaded('generatedReport', function () {
                return [
                    'id' => $this->generatedReport->id,
                    'message' => $this->generatedReport->message,
                    'priority' => $this->generatedReport->priority,
                    'status' => $this->generatedReport->status,
                    'reports_count' => $this->generatedReport->reports_count,
                ];
            }),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}

