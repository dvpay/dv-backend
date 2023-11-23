<?php

declare(strict_types=1);

namespace App\Http\Requests\Processing;

use App\Enums\Blockchain;
use App\Enums\ProcessingCallbackType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

/**
 * ProcessingCallbackRequest
 */
class ProcessingCallbackRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'id'              => ['string', 'nullable'],
            'type'            => [new Enum(ProcessingCallbackType::class), 'nullable'],
            'status'          => ['string', 'nullable'],
            'invoice_id'      => ['string'],
            'tx'              => ['required', 'string'],
            'amount'          => ['string', 'nullable'],
            'blockchain'      => [new Enum(Blockchain::class), 'nullable'],
            'address'         => ['string', 'nullable'],
            'sender'          => ['string', 'nullable'],
            'contractAddress' => ['string', 'nullable'],
            'confirmations'   => ['string', 'nullable'],
            'time'            => ['string', 'nullable'],
            'payer_id'        => ['string', 'nullable'],
            'energy'          => ['sometimes', 'integer', 'nullable'],
            'bandwidth'       => ['sometimes', 'integer', 'nullable'],
            'uuid'            => ['sometimes', 'uuid']
        ];
    }
}
