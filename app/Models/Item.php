<?php

namespace App\Models;

use App\Enums\CategoryEnum;
use App\Enums\UnitEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id',
        'category',
        'name',
        'complement',
        'unit',
        'required_quantity',
        'bagged_quantity',
        'received_quantity',
        'delivery_date',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'category' => CategoryEnum::class,
            'unit' => UnitEnum::class,
            'required_quantity' => 'decimal:1',
            'bagged_quantity' => 'decimal:1',
            'received_quantity' => 'decimal:1',
            'delivery_date' => 'date',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function bags(): HasMany
    {
        return $this->hasMany(BagItem::class);
    }
}
