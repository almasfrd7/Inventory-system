<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * PUT   = full replacement  -> every field required.
     * PATCH = partial update    -> fields optional.
     */
    public function rules(): array
    {
        $presence = $this->isMethod('PATCH') ? 'sometimes' : 'required';

        return [
            'name' => [$presence, 'string', 'max:255'],
            'code' => [
                $presence, 'string', 'max:255',
                Rule::unique('products', 'code')->ignore($this->route('product')),
            ],
            'price' => [$presence, 'numeric', 'min:0', 'max:99999999.99'],
            'stock' => [$presence, 'integer', 'min:0', 'max:2147483647'],
            'description' => 'nullable|string',
        ];
    }
}
