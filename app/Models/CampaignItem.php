<?php

namespace App\Models;

use App\Enums\CategoryEnum;
use App\Enums\UnitEnum;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CampaignItem extends Model
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

    protected function formattedBaggedQuantity(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes): string {
                $bagged_quantity = (float) $attributes['bagged_quantity'];

                if (floor($bagged_quantity) === $bagged_quantity) {
                    return (string) (int) $bagged_quantity;
                }

                return number_format($bagged_quantity, 1, ',', '');
            },
        );
    }

        protected function formattedReceivedQuantity(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes): string {
                $received_quantity = (float) $attributes['received_quantity'];

                if (floor($received_quantity) === $received_quantity) {
                    return (string) (int) $received_quantity;
                }

                return number_format($received_quantity, 1, ',', '');
            },
        );
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function bagItems(): HasMany
    {
        return $this->hasMany(BagItem::class);
    }
}
