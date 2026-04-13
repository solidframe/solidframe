<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class AssignTaskRequest extends FormRequest
{
    /** @return array<string, string> */
    public function rules(): array
    {
        return [
            'assignee' => 'required|string|max:255',
        ];
    }
}
