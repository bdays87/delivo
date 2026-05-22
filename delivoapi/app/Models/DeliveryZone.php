<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryZone extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'ACTIVE';

    public const STATUS_ARCHIVED = 'ARCHIVED';

    protected $fillable = [
        'city',
        'hub_name',
        'hub_address',
        'hub_latitude',
        'hub_longitude',
        'status',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'hub_latitude' => 'decimal:6',
            'hub_longitude' => 'decimal:6',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Composed origin reference for Google Distance Matrix calls. Returns
     * "lat,lng" when coordinates are set, falling back to the hub address
     * or finally the city name.
     */
    public function originRef(): ?string
    {
        if ($this->hub_latitude !== null && $this->hub_longitude !== null) {
            return $this->hub_latitude.','.$this->hub_longitude;
        }
        if ($this->hub_address) {
            return $this->hub_address;
        }

        return $this->city.', Zimbabwe';
    }
}
