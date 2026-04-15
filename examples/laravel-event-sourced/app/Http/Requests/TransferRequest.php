<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class TransferRequest extends FormRequest
{
    /** @return array<string, string> */
    public function rules(): array
    {
        return [
            'target_account_id' => 'required|string|uuid',
            'amount' => 'required|integer|min:1',
            'description' => 'sometimes|string|max:255',
        ];
    }
}
