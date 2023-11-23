<?php

namespace App\Http\Requests\HotWallet;

use Illuminate\Foundation\Http\FormRequest;

class HotWalletsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'page'          => ['numeric', 'nullable'],
            'perPage'       => ['numeric', 'nullable'],
            'hideEmpty'     => ['boolean', 'nullable'],
            'sortDirection' => ['string', 'nullable'],
            'filterField'   => ['string', 'nullable'],
            'filterValue'   => ['string', 'nullable'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
