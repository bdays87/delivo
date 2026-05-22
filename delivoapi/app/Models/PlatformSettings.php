<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlatformSettings extends Model
{
    use HasFactory;

    protected $table = 'platform_settings';

    protected $fillable = [
        'service_charge_pct',
        'service_charge_min_usd',
    ];

    protected function casts(): array
    {
        return [
            'service_charge_pct' => 'decimal:2',
            'service_charge_min_usd' => 'decimal:2',
        ];
    }
}
