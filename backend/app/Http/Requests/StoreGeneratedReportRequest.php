<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGeneratedReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if (! $user) {
            return false;
        }

        return strtoupper((string) $user->role) === 'ADMIN';
    }

    public function rules(): array
    {
        return [
            'report_ids' => ['required', 'array', 'min:1'],
            'report_ids.*' => ['integer', 'distinct', 'exists:reports,id'],
        ];
    }
}

