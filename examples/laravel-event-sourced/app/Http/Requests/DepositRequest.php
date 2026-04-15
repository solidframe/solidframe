<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class DepositRequest extends FormRequest
{
    /** @return array<string, string> */
    public function rules(): array
    {
        return [
            'amount' => 'required|integer|min:1',
            'description' => 'sometimes|string|max:255',
        ];
    }
}
