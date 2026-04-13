<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreTaskRequest extends FormRequest
{
    /** @return array<string, string> */
    public function rules(): array
    {
        return [
            'project_id' => 'required|uuid',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'priority' => 'nullable|string|in:low,medium,high,critical',
        ];
    }
}
