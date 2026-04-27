<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GeneratedReportResource extends JsonResource
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
            'message' => $this->message,
            'priority' => $this->priority,
            'status' => $this->status,
            'reports_count' => $this->reports_count,
            'bde_reason' => $this->bde_reason,
            'created_at' => $this->created_at?->toISOString(),
            'reports' => $this->whenLoaded('reports', function () {
                return \App\Http\Resources\ReportResource::collection($this->reports);
            }),
        ];
        
    }
}

