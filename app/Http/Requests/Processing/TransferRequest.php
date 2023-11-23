<?php

namespace App\Http\Requests\Processing;

use App\Enums\TransferStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class TransferRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'uuid' => ['required', 'string', 'uuid'],
            'status' => ['required', new Enum(TransferStatus::class)]
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
