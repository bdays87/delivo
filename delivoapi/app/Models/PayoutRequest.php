<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayoutRequest extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'PENDING';

    public const STATUS_APPROVED = 'APPROVED';

    public const STATUS_PAID = 'PAID';

    public const STATUS_REJECTED = 'REJECTED';

    public const METHOD_MOBILE_MONEY = 'MOBILE_MONEY';

    public const METHOD_BANK_TRANSFER = 'BANK_TRANSFER';

    protected $fillable = [
        'influencer_id',
        'requested_usd',
        'service_fee_pct',
        'service_fee_usd',
        'net_payout_usd',
        'status',
        'method',
        'destination_label',
        'destination_account',
        'influencer_notes',
        'admin_notes',
        'rejection_reason',
        'processed_by_user_id',
        'processed_at',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'requested_usd' => 'decimal:2',
            'service_fee_pct' => 'decimal:2',
            'service_fee_usd' => 'decimal:2',
            'net_payout_usd' => 'decimal:2',
            'processed_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    public function influencer(): BelongsTo
    {
        return $this->belongsTo(Influencer::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by_user_id');
    }

    public function earnings(): HasMany
    {
        return $this->hasMany(InfluencerEarning::class);
    }
}
