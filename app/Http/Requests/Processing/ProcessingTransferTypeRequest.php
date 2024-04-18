<?php

namespace App\Http\Requests\Processing;

use App\Enums\Blockchain;
use App\Enums\TransferType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class ProcessingTransferTypeRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'blockchain' => ['required', new Enum(Blockchain::class)],
            'type' => ['required', new Enum(TransferType::class)],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
