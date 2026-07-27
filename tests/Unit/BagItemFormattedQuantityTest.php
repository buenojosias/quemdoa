<?php

use App\Models\BagItem;

it('formats rounded quantities without decimal separator', function (float|string $quantity, string $formattedQuantity) {
    $bagItem = new BagItem([
        'quantity' => $quantity,
    ]);

    expect($bagItem->formatted_quantity)->toBe($formattedQuantity);
})->with([
    'integer' => [2, '2'],
    'decimal zero' => ['2.0', '2'],
    'zero' => [0, '0'],
]);

it('formats decimal quantities with one decimal and comma separator', function (float|string $quantity, string $formattedQuantity) {
    $bagItem = new BagItem([
        'quantity' => $quantity,
    ]);

    expect($bagItem->formatted_quantity)->toBe($formattedQuantity);
})->with([
    'half' => [2.5, '2,5'],
    'decimal string' => ['3.7', '3,7'],
]);
