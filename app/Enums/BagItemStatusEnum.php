<?php

namespace App\Enums;

enum BagItemStatusEnum: string
{
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case RECEIVED = 'received';
    case CANCELED = 'canceled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pendente',
            self::CONFIRMED => 'Confirmado',
            self::RECEIVED => 'Recebido',
            self::CANCELED => 'Cancelado',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'yellow',
            self::CONFIRMED => 'blue',
            self::RECEIVED => 'green',
            self::CANCELED => 'red',
        };
    }
}
