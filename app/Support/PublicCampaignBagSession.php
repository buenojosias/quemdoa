<?php

namespace App\Support;

final class PublicCampaignBagSession
{
    private const int TTL_SECONDS = 43200;

    /**
     * @return array<int, array{id: int, name: string, complement: ?string, quantity: float, formattedQuantity: string, pendingBaggedQuantity: float, unitAbbreviation: string, unitLabel: string, deliveryDate: ?string, note: ?string}>
     */
    public static function get(int|string $campaignId): array
    {
        $bagItems = session()->cache()->get(self::key($campaignId), []);

        if (! is_array($bagItems)) {
            return [];
        }

        return array_values(array_map(
            fn (array $bagItem): array => self::normalizeBagItem($bagItem),
            array_filter($bagItems, is_array(...)),
        ));
    }

    /**
     * @param  array<int, array{id: int, name: string, complement: ?string, quantity: float|int|string, formattedQuantity?: string, pendingBaggedQuantity: float|int|string, unitAbbreviation: string, unitLabel: string, deliveryDate: ?string, note?: ?string}>  $bagItems
     */
    public static function put(int|string $campaignId, array $bagItems): void
    {
        if ($bagItems === []) {
            self::forget($campaignId);

            return;
        }

        session()->cache()->put(
            self::key($campaignId),
            array_values(array_map(
                fn (array $bagItem): array => self::normalizeBagItem($bagItem),
                $bagItems,
            )),
            self::TTL_SECONDS,
        );
    }

    public static function forget(int|string $campaignId): void
    {
        session()->cache()->forget(self::key($campaignId));
    }

    private static function key(int|string $campaignId): string
    {
        return 'public-campaign-bag.'.$campaignId;
    }

    /**
     * @param  array{id: int, name: string, complement?: ?string, quantity: float|int|string, formattedQuantity?: string, pendingBaggedQuantity: float|int|string, unitAbbreviation: string, unitLabel: string, deliveryDate?: ?string, note?: ?string}  $bagItem
     * @return array{id: int, name: string, complement: ?string, quantity: float, formattedQuantity: string, pendingBaggedQuantity: float, unitAbbreviation: string, unitLabel: string, deliveryDate: ?string, note: ?string}
     */
    private static function normalizeBagItem(array $bagItem): array
    {
        $quantity = (float) $bagItem['quantity'];

        return [
            'id' => (int) $bagItem['id'],
            'name' => $bagItem['name'],
            'complement' => $bagItem['complement'] ?? null,
            'quantity' => $quantity,
            'formattedQuantity' => $bagItem['formattedQuantity'] ?? self::formatQuantity($quantity),
            'pendingBaggedQuantity' => (float) $bagItem['pendingBaggedQuantity'],
            'unitAbbreviation' => $bagItem['unitAbbreviation'],
            'unitLabel' => $bagItem['unitLabel'],
            'deliveryDate' => $bagItem['deliveryDate'] ?? null,
            'note' => $bagItem['note'] ?? null,
        ];
    }

    private static function formatQuantity(float $quantity): string
    {
        if (floor($quantity) === $quantity) {
            return (string) (int) $quantity;
        }

        return number_format($quantity, 1, ',', '');
    }
}
