<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExchangeApiKey extends Model
{
    protected $fillable = [
        'exchange_id',
        'api_key',
        'api_secret',
    ];

    // Скрываем секретные данные из массивов/JSON
    protected $hidden = [
        'api_key',
        'api_secret',
    ];

    public function exchange(): BelongsTo
    {
        return $this->belongsTo(Exchange::class);
    }
}
