<?php

namespace App\Enums;

enum BagItemStatusEnum: string
{
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case RECEIVED = 'received';
    case CANCELED = 'canceled';
}
