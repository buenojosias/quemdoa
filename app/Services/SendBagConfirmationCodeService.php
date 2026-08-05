<?php

namespace App\Services;

use App\Models\Bag;
use App\Models\BagItem;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class SendBagConfirmationCodeService
{
    public function send(Bag $bag): Response
    {
        $bag->loadMissing(['campaign', 'items.item']);

        $baseUrl = $this->baseUrl();
        $apiKey = config('services.evolution.api_key');
        $instance = config('services.evolution.instance');

        if (! is_string($apiKey) || $apiKey === '' || ! is_string($instance) || $instance === '') {
            throw new RuntimeException('Evolution API is not configured.');
        }

        return Http::baseUrl($baseUrl)
            ->acceptJson()
            ->asJson()
            ->withHeaders([
                'apikey' => $apiKey,
            ])
            ->timeout(10)
            ->connectTimeout(5)
            ->post('/message/sendText/'.rawurlencode($instance), [
                'number' => $this->whatsappNumber($bag),
                'text' => $this->message($bag),
                'linkPreview' => false,
            ])
            ->throw();
    }

    private function baseUrl(): string
    {
        $baseUrl = config('services.evolution.base_url');

        if (! is_string($baseUrl) || trim($baseUrl) === '') {
            throw new RuntimeException('EVOLUTION_API_URL is not configured.');
        }

        $baseUrl = rtrim(trim($baseUrl), '/');

        if (! Str::startsWith($baseUrl, ['http://', 'https://']) || parse_url($baseUrl, PHP_URL_HOST) === null) {
            throw new RuntimeException('EVOLUTION_API_URL must be an absolute URL with http:// or https://.');
        }

        return $baseUrl;
    }

    private function whatsappNumber(Bag $bag): string
    {
        $whatsapp = preg_replace('/\D/', '', (string) $bag->participant_whatsapp) ?? '';
        $countryCode = (string) config('services.evolution.country_code', '55');

        if ($countryCode !== '' && ! str_starts_with($whatsapp, $countryCode)) {
            return $countryCode.$whatsapp;
        }

        return $whatsapp;
    }

    private function message(Bag $bag): string
    {
        $firstName = Str::of($bag->participant_name)->trim()->explode(' ')->first();
        return <<<MESSAGE
Olá, {$firstName}! 😊
Muito obrigado pela sua generosidade e por colaborar com a campanha {$bag->campaign->name}.

Para confirmar sua sacola, use o código:

{$bag->confirmation_code}

Itens selecionados:
{$this->itemsList($bag)}

Após a confirmação, sua contribuição será registrada na campanha. Que Deus abençoe sua disposição em ajudar! 🙏
MESSAGE;
    }

    private function itemsList(Bag $bag): string
    {
        return $bag->items
            ->map(fn (BagItem $bagItem): string => sprintf(
                '- %s %s %s',
                $bagItem->formatted_quantity,
                $bagItem->item->unit->abbreviation(),
                $bagItem->item->name,
            ))
            ->implode(PHP_EOL);
    }
}
