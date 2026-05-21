<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vendor extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'PENDING';

    public const STATUS_ACTIVE = 'ACTIVE';

    public const STATUS_REJECTED = 'REJECTED';

    public const STATUS_SUSPENDED = 'SUSPENDED';

    protected $fillable = [
        'owner_user_id',
        'business_name',
        'slug',
        'support_email',
        'support_phone',
        'tin',
        'registration_no',
        'commission_pct_override',
        'status',
        'rejection_reason',
        'approved_at',
        'rejected_at',
        'suspended_at',
    ];

    protected function casts(): array
    {
        return [
            'commission_pct_override' => 'decimal:2',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'suspended_at' => 'datetime',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function kycDocuments(): HasMany
    {
        return $this->hasMany(VendorKycDocument::class);
    }

    public function payoutAccounts(): HasMany
    {
        return $this->hasMany(VendorPayoutAccount::class)->orderByDesc('is_primary')->latest();
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
