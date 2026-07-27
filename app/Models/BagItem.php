<?php

namespace App\Models;

use App\Enums\BagItemStatusEnum;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BagItem extends Model
{
    protected $fillable = [
        'bag_id',
        'campaign_item_id',
        'quantity',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:1',
            'status' => BagItemStatusEnum::class,
        ];
    }

    protected function formattedQuantity(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes): string {
                $quantity = (float) $attributes['quantity'];

                if (floor($quantity) === $quantity) {
                    return (string) (int) $quantity;
                }

                return number_format($quantity, 1, ',', '');
            },
        );
    }

    public function bag(): BelongsTo
    {
        return $this->belongsTo(Bag::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(CampaignItem::class, 'campaign_item_id');
    }
}
