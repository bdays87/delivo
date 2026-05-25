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

    public const DELIVERY_PENDING = 'PENDING';

    public const DELIVERY_AWAITING_DROPOFF = 'AWAITING_DROPOFF';

    public const DELIVERY_DROPOFF_INITIATED = 'DROPOFF_INITIATED';

    public const DELIVERY_AWAITING_DISPATCH = 'AWAITING_DISPATCH';

    public const DELIVERY_READY_FOR_PICKUP = 'READY_FOR_PICKUP';

    public const DELIVERY_INROUTE = 'INROUTE';

    public const DELIVERY_DELIVERED = 'DELIVERED';

    public const METHOD_HOME_DELIVERY = 'HOME_DELIVERY';

    public const METHOD_SELF_PICKUP = 'SELF_PICKUP';

    protected $fillable = [
        'order_number',
        'user_id',
        'address_id',
        'mobile_wallet_id',
        'delivery_method',
        'ship_recipient_name',
        'ship_recipient_phone',
        'ship_city',
        'ship_suburb',
        'ship_street',
        'ship_notes',
        'status',
        'delivery_status',
        'subtotal_usd',
        'total_buyer_discount_usd',
        'total_influencer_commission_usd',
        'service_charge_usd',
        'shipping_usd',
        'total_usd',
        'applied_coupon_id',
        'applied_coupon_code',
        'usd_to_zwg_rate',
        'payment_reference',
        'delivery_code',
        'payment_confirmed_at',
        'delivered_at',
        'customer_delivery_confirmed_at',
        'cancellation_reason',
    ];

    protected function casts(): array
    {
        return [
            'subtotal_usd' => 'decimal:2',
            'total_buyer_discount_usd' => 'decimal:2',
            'total_influencer_commission_usd' => 'decimal:2',
            'service_charge_usd' => 'decimal:2',
            'shipping_usd' => 'decimal:2',
            'total_usd' => 'decimal:2',
            'usd_to_zwg_rate' => 'decimal:6',
            'payment_confirmed_at' => 'datetime',
            'delivered_at' => 'datetime',
            'customer_delivery_confirmed_at' => 'datetime',
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
