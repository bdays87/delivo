<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryProviderRoute extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'ACTIVE';

    public const STATUS_ARCHIVED = 'ARCHIVED';

    protected $fillable = [
        'delivery_provider_id',
        'origin_city',
        'destination_city',
        'waypoints',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'waypoints' => 'array',
        ];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(DeliveryProvider::class, 'delivery_provider_id');
    }

    /**
     * Ordered list of cities the route passes through, lowercased + trimmed
     * for case-insensitive matching against an order's pickup/destination.
     *
     * @return array<int, string>
     */
    public function citySequence(): array
    {
        $cities = array_merge(
            [$this->origin_city],
            is_array($this->waypoints) ? $this->waypoints : [],
            [$this->destination_city],
        );

        return array_values(array_map(
            fn ($c) => mb_strtolower(trim((string) $c)),
            $cities,
        ));
    }
}
