<?php

namespace App\Http\Requests\Store;

use Illuminate\Foundation\Http\FormRequest;

class ChangeStatusStoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'status' => ['required', 'boolean']
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
