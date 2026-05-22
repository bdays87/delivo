<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryProviderKycDocument extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'PENDING';

    public const STATUS_APPROVED = 'APPROVED';

    public const STATUS_REJECTED = 'REJECTED';

    protected $fillable = [
        'delivery_provider_id',
        'type',
        'original_filename',
        'disk',
        'path',
        'size_bytes',
        'mime_type',
        'status',
    ];

    public function provider(): BelongsTo
    {
        return $this->belongsTo(DeliveryProvider::class, 'delivery_provider_id');
    }
}
