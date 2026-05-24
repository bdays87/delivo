<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Coupon extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'ACTIVE';

    public const STATUS_ARCHIVED = 'ARCHIVED';

    protected $fillable = [
        'code',
        'vendor_id',
        'product_id',
        'influencer_id',
        'buyer_discount_pct',
        'influencer_commission_pct',
        'usage_limit',
        'usage_count',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'buyer_discount_pct' => 'decimal:2',
            'influencer_commission_pct' => 'decimal:2',
            'usage_limit' => 'integer',
            'usage_count' => 'integer',
        ];
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function influencer(): BelongsTo
    {
        return $this->belongsTo(Influencer::class);
    }

    public function isInfluencerCode(): bool
    {
        return $this->influencer_id !== null;
    }
}
