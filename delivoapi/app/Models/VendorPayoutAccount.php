<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorPayoutAccount extends Model
{
    use HasFactory;

    public const TYPE_MOBILE_WALLET = 'MOBILE_WALLET';

    public const TYPE_BANK_TRANSFER = 'BANK_TRANSFER';

    public const STATUS_ACTIVE = 'ACTIVE';

    public const STATUS_ARCHIVED = 'ARCHIVED';

    protected $fillable = [
        'vendor_id',
        'type',
        'label',
        'mobile_wallet_id',
        'mobile_wallet_msisdn',
        'bank_name',
        'bank_account_name',
        'bank_account_number',
        'bank_currency',
        'is_primary',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
        ];
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function mobileWallet(): BelongsTo
    {
        return $this->belongsTo(MobileWallet::class);
    }
}
