<?php

namespace App\Http\Requests\Setup;

use Illuminate\Foundation\Http\FormRequest;

class SetupRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'database_connection' => ['required', 'in:mysql'],
            'database_hostname'   => ['required', 'min:1'],
            'database_port'       => ['required', 'min:3'],
            'database_name'       => ['required'],
            'database_username'   => ['required'],
            'database_password'   => ['required'],
            'redis_host'          => ['required'],
            'redis_password'      => ['sometimes', 'nullable'],
            'redis_port'          => ['required'],
            'processing_host'     => ['required', 'url']
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
