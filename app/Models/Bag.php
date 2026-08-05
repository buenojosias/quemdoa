<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bag extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'campaign_id',
        'code',
        'user_id',
        'participant_name',
        'participant_whatsapp',
        'confirmation_code',
        'notes',
        'confirmed_by',
        'confirmed_at',
        'received_at',
    ];

    protected function casts(): array
    {
        return [
            'confirmed_at' => 'datetime',
            'received_at' => 'datetime',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(BagItem::class);
    }
}
