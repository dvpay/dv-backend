<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceHistory extends Model
{
    protected $fillable = [
        'invoice_id',
        'text',
        'text_variables',
    ];

    protected $casts = [
        'text_variables' => 'json'
    ];
}
