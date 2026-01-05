<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exchange extends Model
{
    protected $fillable = [
        'name',
        'api_base_url',
        'spot_api_url',
        'futures_api_url',
        'kline_api_url',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function apiKeys(): HasMany
    {
        return $this->hasMany(ExchangeApiKey::class);
    }

    public function currencies(): HasMany
    {
        return $this->hasMany(ExchangeCurrency::class);
    }

    public function exchangePairs(): HasMany
    {
        return $this->hasMany(ExchangePair::class);
    }
}
