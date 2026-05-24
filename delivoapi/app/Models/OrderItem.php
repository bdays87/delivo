<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'vendor_id',
        'product_id',
        'product_variant_id',
        'influencer_id',
        'product_name_snapshot',
        'color_snapshot',
        'quantity',
        'unit_price_usd_snapshot',
        'buyer_discount_pct_snapshot',
        'line_discount_usd_snapshot',
        'influencer_commission_pct_snapshot',
        'line_commission_usd_snapshot',
        'line_total_usd_snapshot',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price_usd_snapshot' => 'decimal:2',
            'buyer_discount_pct_snapshot' => 'decimal:2',
            'line_discount_usd_snapshot' => 'decimal:2',
            'influencer_commission_pct_snapshot' => 'decimal:2',
            'line_commission_usd_snapshot' => 'decimal:2',
            'line_total_usd_snapshot' => 'decimal:2',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function influencer(): BelongsTo
    {
        return $this->belongsTo(Influencer::class);
    }
}
