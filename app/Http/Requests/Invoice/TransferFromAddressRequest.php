<?php

declare(strict_types=1);

namespace App\Http\Requests\Invoice;

use Illuminate\Foundation\Http\FormRequest;

/**
 * TransferFromAddressRequest
 */
class TransferFromAddressRequest extends FormRequest
{
	/**
	 * @return array
	 */
	public function rules(): array
	{
		return [
			'addressFrom' => ['required', 'string', 'min:12', 'max:64'],
			'currencyId'  => ['required', 'string', 'min:1', 'max:255', 'exists:currencies,id'],
		];
	}
}
