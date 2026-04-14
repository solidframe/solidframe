<?php

declare(strict_types=1);

namespace Modules\Inventory\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class AddProductRequest extends FormRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:1', 'max:255'],
            'sku' => ['required', 'string', 'min:1', 'max:100'],
            'stock' => ['required', 'integer', 'min:0'],
            'price' => ['required', 'integer', 'min:1'],
        ];
    }
}
