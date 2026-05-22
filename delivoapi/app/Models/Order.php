<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    public const STATUS_PENDING_PAYMENT = 'PENDING_PAYMENT';

    public const STATUS_PAID = 'PAID';

    public const STATUS_PICKED_UP = 'PICKED_UP';

    public const STATUS_OUT_FOR_DELIVERY = 'OUT_FOR_DELIVERY';

    public const STATUS_DELIVERED = 'DELIVERED';

    public const STATUS_COMPLETED = 'COMPLETED';

    public const STATUS_CANCELLED = 'CANCELLED';

    public const STATUS_REFUNDED = 'REFUNDED';

    protected $fillable = [
        'order_number',
        'user_id',
        'address_id',
        'mobile_wallet_id',
        'ship_recipient_name',
        'ship_recipient_phone',
        'ship_city',
        'ship_suburb',
        'ship_street',
        'ship_notes',
        'status',
        'subtotal_usd',
        'service_charge_usd',
        'shipping_usd',
        'total_usd',
        'usd_to_zwg_rate',
        'payment_reference',
        'payment_confirmed_at',
        'cancellation_reason',
    ];

    protected function casts(): array
    {
        return [
            'subtotal_usd' => 'decimal:2',
            'service_charge_usd' => 'decimal:2',
            'shipping_usd' => 'decimal:2',
            'total_usd' => 'decimal:2',
            'usd_to_zwg_rate' => 'decimal:6',
            'payment_confirmed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function mobileWallet(): BelongsTo
    {
        return $this->belongsTo(MobileWallet::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(OrderDeliveryShipment::class);
    }
}
