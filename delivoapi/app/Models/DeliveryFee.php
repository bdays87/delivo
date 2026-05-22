<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryFee extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'ACTIVE';

    public const STATUS_ARCHIVED = 'ARCHIVED';

    protected $fillable = [
        'min_km',
        'max_km',
        'fee_usd',
        'sort_order',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'min_km' => 'integer',
            'max_km' => 'integer',
            'fee_usd' => 'decimal:2',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Inclusive on both ends; null max_km is "and up".
     */
    public function covers(float $km): bool
    {
        if ($this->status !== self::STATUS_ACTIVE) {
            return false;
        }
        if ($km < $this->min_km) {
            return false;
        }
        if ($this->max_km === null) {
            return true;
        }

        return $km <= $this->max_km;
    }
}
