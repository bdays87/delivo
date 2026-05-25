<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderDeliveryShipment extends Model
{
    use HasFactory;

    public const STATUS_AWAITING_PROVIDER = 'AWAITING_PROVIDER';

    public const STATUS_ASSIGNED = 'ASSIGNED';

    public const STATUS_PICKED_UP = 'PICKED_UP';

    public const STATUS_OUT_FOR_DELIVERY = 'OUT_FOR_DELIVERY';

    public const STATUS_DELIVERED = 'DELIVERED';

    public const STATUS_CANCELLED = 'CANCELLED';

    protected $fillable = [
        'order_id',
        'vendor_id',
        'hub_id',
        'delivery_provider_id',
        'hub_name_snapshot',
        'hub_address_snapshot',
        'distance_km',
        'fee_usd',
        'delivery_fee_id',
        'shipment_status',
        'dropoff_deadline',
        'dropoff_initiated_at',
        'dropped_off_at',
        'assigned_at',
        'picked_up_at',
        'out_for_delivery_at',
        'delivered_at',
    ];

    protected function casts(): array
    {
        return [
            'distance_km' => 'decimal:2',
            'fee_usd' => 'decimal:2',
            'dropoff_deadline' => 'datetime',
            'dropoff_initiated_at' => 'datetime',
            'dropped_off_at' => 'datetime',
            'assigned_at' => 'datetime',
            'picked_up_at' => 'datetime',
            'out_for_delivery_at' => 'datetime',
            'delivered_at' => 'datetime',
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

    public function provider(): BelongsTo
    {
        return $this->belongsTo(DeliveryProvider::class, 'delivery_provider_id');
    }
}
