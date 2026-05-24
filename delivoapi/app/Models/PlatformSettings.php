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
        'affiliate_total_min_pct',
        'affiliate_total_max_pct',
        'influencer_payout_fee_pct',
        'influencer_payout_fee_min_usd',
    ];

    protected function casts(): array
    {
        return [
            'service_charge_pct' => 'decimal:2',
            'service_charge_min_usd' => 'decimal:2',
            'affiliate_total_min_pct' => 'decimal:2',
            'affiliate_total_max_pct' => 'decimal:2',
            'influencer_payout_fee_pct' => 'decimal:2',
            'influencer_payout_fee_min_usd' => 'decimal:2',
        ];
    }
}
