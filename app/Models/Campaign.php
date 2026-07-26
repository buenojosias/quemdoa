<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Kra8\Snowflake\HasSnowflakePrimary;

class Campaign extends Model
{
    use HasSnowflakePrimary, HasFactory;
    
    protected $fillable = [
        'user_id',
        'name',
        'description',
        'confirmation_deadline',
        'delivery_deadline',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'confirmation_deadline' => 'datetime: d/m/Y',
            'delivery_deadline' => 'datetime: d/m/Y',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    public function bags(): HasMany
    {
        return $this->hasMany(Bag::class);
    }

    public function bagItems(): HasManyThrough
    {
        return $this->hasManyThrough(BagItem::class, Item::class);
    }
}
