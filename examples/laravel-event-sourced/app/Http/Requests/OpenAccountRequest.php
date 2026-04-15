<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class OpenAccountRequest extends FormRequest
{
    /** @return array<string, string> */
    public function rules(): array
    {
        return [
            'holder_name' => 'required|string|min:2|max:255',
            'currency' => 'required|string|in:TRY,USD,EUR',
            'initial_balance' => 'sometimes|integer|min:0',
        ];
    }
}
