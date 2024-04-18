<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Webhook extends Model
{
    use HasUuid, HasFactory;

    protected $primaryKey = 'id';

    protected $fillable = [
        'store_id',
        'url',
        'secret',
        'enabled',
        'events',
    ];

    protected $casts = [
        'events' => 'json',
        'enabled' => 'boolean',
    ];
}