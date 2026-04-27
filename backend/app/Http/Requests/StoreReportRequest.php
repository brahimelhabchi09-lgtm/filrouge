<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if (! $user) {
            return false;
        }

        // Keep business rule consistent with routes: only STUDENT can create reports.
        return strtoupper((string) $user->role) === 'STUDENT';
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'min:10'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
        ];
    }
}

