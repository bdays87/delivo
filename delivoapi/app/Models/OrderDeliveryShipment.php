<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderDeliveryShipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'vendor_id',
        'hub_id',
        'hub_name_snapshot',
        'hub_address_snapshot',
        'distance_km',
        'fee_usd',
        'delivery_fee_id',
    ];

    protected function casts(): array
    {
        return [
            'distance_km' => 'decimal:2',
            'fee_usd' => 'decimal:2',
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

    public function hub(): BelongsTo
    {
        return $this->belongsTo(DeliveryZone::class, 'hub_id');
    }
}
