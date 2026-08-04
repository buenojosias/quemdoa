<?php

use App\Models\User;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use TallStackUi\Traits\Interactions;

new class extends Component
{
    use Interactions;

    public const DISMISSED_KEY = 'dashboard_whatsapp_alert_dismissed';

    public bool $hidden = false;

    public bool $modal = false;

    public bool $editingWhatsapp = false;

    public bool $codeSent = false;

    public string $whatsapp = '';

    public string $userFirstName = '';

    public string $pin = '';

    public ?string $whatsappDeliveryError = null;

    public function mount(): void
    {
        $user = $this->user();

        $this->hidden = $user === null
            || $user->whatsapp_verified_at !== null
            || session(self::DISMISSED_KEY) === true
            || request()->cookie(self::DISMISSED_KEY) === '1';

        $this->whatsapp = $this->formatWhatsapp((string) $user?->whatsapp);
        $this->userFirstName = $user?->name ? explode(' ', $user->name)[0] : '';
    }

    public function dismiss(): void
    {
        session()->put(self::DISMISSED_KEY, true);
        Cookie::queue(self::DISMISSED_KEY, '1', 60 * 24 * 5);

        $this->hidden = true;
        $this->dispatch('dashboard-whatsapp-alert-dismissed');
    }

    public function openModal(): void
    {
        $this->resetValidation();
        $this->whatsappDeliveryError = null;
        $this->pin = '';
        $this->codeSent = false;
        $this->editingWhatsapp = blank($this->user()?->whatsapp);
        $this->whatsapp = $this->formatWhatsapp((string) $this->user()?->whatsapp);
        $this->modal = true;
    }

    public function editWhatsapp(): void
    {
        $this->resetValidation(['whatsapp', 'pin']);
        $this->whatsappDeliveryError = null;
        $this->pin = '';
        $this->codeSent = false;
        $this->editingWhatsapp = true;
    }

    public function useExistingCode(): void
    {
        $user = $this->user();

        if ($user === null) {
            abort(403);
        }

        if (blank($user->whatsapp_confirmation_code)) {
            throw ValidationException::withMessages([
                'pin' => 'Solicite um código antes de confirmar seu WhatsApp.',
            ]);
        }

        $this->resetValidation('pin');
        $this->whatsappDeliveryError = null;
        $this->pin = '';
        $this->editingWhatsapp = false;
        $this->codeSent = true;
    }

    public function sendCode(): void
    {
        $user = $this->user();

        if ($user === null) {
            abort(403);
        }

        $this->whatsappDeliveryError = null;
        $this->pin = '';

        if ($this->editingWhatsapp || blank($user->whatsapp)) {
            $this->whatsapp = $this->normalizeWhatsapp($this->whatsapp);

            $validated = $this->validate([
                'whatsapp' => [
                    'required',
                    'digits_between:10,11',
                    Rule::unique(User::class, 'whatsapp')->ignore($user->id),
                ],
            ]);

            if (! $this->isValidWhatsapp($validated['whatsapp'])) {
                throw ValidationException::withMessages([
                    'whatsapp' => 'Informe um WhatsApp válido.',
                ]);
            }

            $user->forceFill([
                'whatsapp' => $validated['whatsapp'],
                'whatsapp_verified_at' => null,
            ])->save();
        }

        $confirmationCode = $this->generateConfirmationCode();

        $user->forceFill([
            'whatsapp_confirmation_code' => $confirmationCode,
        ])->save();

        try {
            $this->sendConfirmationCode($user, $confirmationCode);
        } catch (\Throwable $exception) {
            $this->logConfirmationCodeDeliveryFailure($user, $exception);

            $this->whatsappDeliveryError = 'Não conseguimos enviar o código pelo WhatsApp agora. Tente novamente em instantes.';

            return;
        }

        $this->whatsapp = $this->formatWhatsapp((string) $user->whatsapp);
        $this->editingWhatsapp = false;
        $this->userFirstName = $user->name ? explode(' ', $user->name)[0] : '';
        $this->codeSent = true;
    }

    public function confirmCode(): void
    {
        $this->validateOnly('pin');

        $user = $this->user();

        if ($user === null) {
            abort(403);
        }

        if (! hash_equals((string) $user->whatsapp_confirmation_code, $this->pin)) {
            throw ValidationException::withMessages([
                'pin' => 'O código informado está incorreto.',
            ]);
        }

        $user->forceFill([
            'whatsapp_verified_at' => now(),
            'whatsapp_confirmation_code' => null,
        ])->save();

        session()->forget(self::DISMISSED_KEY);
        Cookie::queue(Cookie::forget(self::DISMISSED_KEY));

        $this->modal = false;
        $this->hidden = true;
        $this->toast()->success('WhatsApp confirmado com sucesso!')->send();
        $this->dispatch('dashboard-whatsapp-confirmed');
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'pin' => [
                'required',
                'digits:5',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function validationAttributes(): array
    {
        return [
            'whatsapp' => 'WhatsApp',
            'pin' => 'código',
        ];
    }

    private function user(): ?User
    {
        $user = Auth::user();

        return $user instanceof User ? $user : null;
    }

    private function normalizeWhatsapp(string $whatsapp): string
    {
        return preg_replace('/\D/', '', $whatsapp) ?? '';
    }

    private function formatWhatsapp(string $whatsapp): string
    {
        $whatsapp = $this->normalizeWhatsapp($whatsapp);

        if (strlen($whatsapp) === 11) {
            return sprintf('(%s) %s-%s', substr($whatsapp, 0, 2), substr($whatsapp, 2, 5), substr($whatsapp, 7));
        }

        if (strlen($whatsapp) === 10) {
            return sprintf('(%s) %s-%s', substr($whatsapp, 0, 2), substr($whatsapp, 2, 4), substr($whatsapp, 6));
        }

        return $whatsapp;
    }

    private function isValidWhatsapp(string $whatsapp): bool
    {
        if (! preg_match('/^\d{10,11}$/', $whatsapp)) {
            return false;
        }

        return count(array_unique(str_split($whatsapp))) > 1;
    }

    private function generateConfirmationCode(): string
    {
        return str_pad((string) random_int(0, 99999), 5, '0', STR_PAD_LEFT);
    }

    private function sendConfirmationCode(User $user, string $confirmationCode): Response
    {
        $baseUrl = $this->evolutionBaseUrl();
        $apiKey = config('services.evolution.api_key');
        $instance = config('services.evolution.instance');

        if (! is_string($apiKey) || $apiKey === '' || ! is_string($instance) || $instance === '') {
            throw new \RuntimeException('Evolution API is not configured.');
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
                'number' => $this->whatsappNumber((string) $user->whatsapp),
                'text' => $this->message($user, $confirmationCode),
                'linkPreview' => false,
            ])
            ->throw();
    }

    private function evolutionBaseUrl(): string
    {
        $baseUrl = config('services.evolution.base_url');

        if (! is_string($baseUrl) || trim($baseUrl) === '') {
            throw new \RuntimeException('EVOLUTION_API_URL is not configured.');
        }

        $baseUrl = rtrim(trim($baseUrl), '/');

        if (! Str::startsWith($baseUrl, ['http://', 'https://']) || parse_url($baseUrl, PHP_URL_HOST) === null) {
            throw new \RuntimeException('EVOLUTION_API_URL must be an absolute URL with http:// or https://.');
        }

        return $baseUrl;
    }

    private function whatsappNumber(string $whatsapp): string
    {
        $whatsapp = $this->normalizeWhatsapp($whatsapp);
        $countryCode = (string) config('services.evolution.country_code', '55');

        if ($countryCode !== '' && ! str_starts_with($whatsapp, $countryCode)) {
            return $countryCode.$whatsapp;
        }

        return $whatsapp;
    }

    private function message(User $user, string $confirmationCode): string
    {
        return <<<MESSAGE
Olá, {$this->userFirstName}!

Use o código abaixo para confirmar seu WhatsApp no QuemDoa:

{$confirmationCode}

Se você não solicitou este código, ignore esta mensagem.
MESSAGE;
    }

    private function logConfirmationCodeDeliveryFailure(User $user, \Throwable $exception): void
    {
        $response = $exception instanceof RequestException
            ? $exception->response
            : null;

        Log::error('Failed to send dashboard WhatsApp confirmation code through Evolution API.', [
            'user_id' => $user->id,
            'whatsapp' => $user->whatsapp,
            'evolution_instance' => config('services.evolution.instance'),
            'exception' => get_class($exception),
            'message' => $exception->getMessage(),
            'status' => $response?->status(),
            'response' => $response?->json() ?? $response?->body(),
        ]);
    }
};
?>

<div>
    @unless ($this->hidden)
        <div class="rounded-lg border border-amber-400 bg-amber-200/40 p-4 shadow-sm dark:border-amber-500/60 dark:bg-amber-600/40">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-start gap-4">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-amber-200/50 text-amber-600 dark:bg-amber-500/20 dark:text-amber-300">
                        <x-icon name="phone" class="h-7 w-7" />
                    </div>
                    <div>
                        <h2 class="text-base font-semibold text-slate-900 dark:text-white">Adicione e confirme seu WhatsApp</h2>
                        <p class="mt-1 max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-300">
                            Assim você confirma doações com mais segurança e recebe avisos importantes sobre suas campanhas.
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3 md:shrink-0">
                    <x-button text="Adicionar WhatsApp" color="amber" outline wire:click="openModal" />
                    <x-button icon="x-mark" color="dark" flat wire:click="dismiss" />
                </div>
            </div>
        </div>
    @endunless

    <x-modal wire="modal" title="Confirmar WhatsApp" size="sm" center>
        <div class="space-y-4">
            @if ($this->whatsappDeliveryError)
                <div class="rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800 dark:border-amber-800 dark:bg-amber-950 dark:text-amber-200">
                    {{ $this->whatsappDeliveryError }}
                </div>
            @endif

            @if (filled(auth()->user()?->whatsapp) && ! $this->editingWhatsapp)
                <div class="rounded-lg border border-gray-200 bg-gray-50 p-3 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
                    Enviaremos o código para <span class="font-semibold">{{ $this->whatsapp }}</span>.
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <x-button text="Reenviar código" wire:click="sendCode" loading="sendCode" />
                    <x-button text="Alterar número" color="gray" outline wire:click="editWhatsapp" />
                    @if (filled(auth()->user()?->whatsapp_confirmation_code))
                        <div class="col-span-2">
                            <x-button text="Já tenho um código" color="neutral" outline wire:click="useExistingCode" flat sm block />
                        </div>
                    @endif
                </div>
            @else
                <form id="dashboard-whatsapp-send-form" wire:submit="sendCode" class="space-y-4">
                    <x-input
                        label="Número de WhatsApp *"
                        wire:model.live.blur="whatsapp"
                        placeholder="(99) 99999-9999"
                        x-mask="(99) 99999-9999"
                        required />
                </form>
            @endif

            @if ($this->codeSent)
                <form id="dashboard-whatsapp-confirm-form" wire:submit="confirmCode" class="space-y-4">
                    <p class="text-sm text-gray-600 dark:text-gray-300">
                        Informe o código de 5 dígitos enviado para o WhatsApp.
                    </p>

                    <x-pin
                        label="Código"
                        wire:model.live="pin"
                        :length="5"
                        smart
                        numbers />
                </form>
            @endif
        </div>

        <x-slot:footer>
            <x-button text="Cancelar" color="gray" wire:click="$set('modal', false)" />

            @if ($this->codeSent)
                <x-button type="submit" form="dashboard-whatsapp-confirm-form" text="Confirmar código" loading="confirmCode" />
            @elseif (blank(auth()->user()?->whatsapp) || $this->editingWhatsapp)
                <x-button type="submit" form="dashboard-whatsapp-send-form" text="Enviar código" loading="sendCode" />
            @endif
        </x-slot:footer>
    </x-modal>
</div>
