<?php

namespace App\Services;

class GenerateBagCodeService
{
    public function generateUniqueCode(string $participantName, string|null $campaignName = null): string
    {
        if (!is_null($campaignName)) {
            $firstLetter = strtoupper(substr($campaignName, 0, 1));
        } else {
            $firstLetter = chr(rand(65, 90));
        }
        $secondLetter = strtoupper(substr($participantName, 0, 1));

        $code = $firstLetter . $secondLetter . str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);

        return $code;
    }
}