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
        'fee_usd',
        'status',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'fee_usd' => 'decimal:2',
            'sort_order' => 'integer',
        ];
    }
}
