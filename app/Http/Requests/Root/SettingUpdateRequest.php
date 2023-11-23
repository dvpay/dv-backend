<?php

namespace App\Http\Requests\Root;

use App\Facades\Settings;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SettingUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'key' => ['required', 'string', Rule::in(Settings::allWithDefaults()->pluck('name'))],
            'value' => ['required']
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
