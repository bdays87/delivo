<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryProvider extends Model
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
        'base_city',
        'vehicle_types',
        'status',
        'rejection_reason',
        'approved_at',
        'rejected_at',
        'suspended_at',
    ];

    protected function casts(): array
    {
        return [
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
        return $this->hasMany(DeliveryProviderKycDocument::class);
    }

    public function coverageAreas(): BelongsToMany
    {
        return $this->belongsToMany(
            DeliveryZone::class,
            'delivery_provider_coverage_areas',
            'delivery_provider_id',
            'delivery_zone_id',
        )->withTimestamps();
    }
}
