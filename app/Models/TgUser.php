<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TgUser extends Model
{
    use HasUuid;
    use SoftDeletes;

    public $incrementing = false;

    protected $fillable = [
        'username',
        'chat_id',
        'user_id',
        'deleted_at',
    ];

    protected $casts = [
        'created_at' => 'datetime:DATE_ATOM',
        'updated_at' => 'datetime:DATE_ATOM',
        'deleted_at' => 'datetime:DATE_ATOM',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}