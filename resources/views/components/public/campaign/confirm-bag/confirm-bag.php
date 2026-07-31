<?php

use App\Enums\BagItemStatusEnum;
use App\Models\Bag;
use App\Models\Campaign;
use App\Models\CampaignItem;
use App\Services\GenerateBagCodeService;
use App\Services\SendBagConfirmationCodeService;
use App\Support\PublicCampaignBagSession;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

new class () extends Component {
    #[Locked]
    public string $campaignId;

    /**
     * @var array<int, array{id: int, name: string, complement: ?string, quantity: float, formattedQuantity: string, pendingBaggedQuantity: float, unitAbbreviation: string, unitLabel: string, deliveryDate: ?string}>
     */
    public array $bagItems = [];

    public bool $modalConfirm = false;

    public bool $pinModal = false;

    public string $participant_name = '';

    public string $participant_whatsapp = '';

    public string $method = 'whatsapp';

    public string $pin = '';

    public ?int $bagId = null;

    public ?string $code = null;

    public ?string $whatsappDeliveryError = null;

    public function mount(int|string $campaignId): void
    {
        $this->campaignId = (string) $campaignId;
    }

    /**
     * @param  array<int, array{id: int, name: string, complement: ?string, quantity: float|int|string, formattedQuantity?: string, pendingBaggedQuantity: float|int|string, unitAbbreviation: string, unitLabel: string, deliveryDate: ?string}>  $bagItems
     */
    #[On('open-public-campaign-confirm-bag.{campaignId}')]
    public function open(array $bagItems): void
    {
        $this->resetForm();

        $this->bagItems = array_values(array_map(
            fn (array $bagItem): array => [
                'id' => (int) $bagItem['id'],
                'name' => $bagItem['name'],
                'complement' => $bagItem['complement'] ?? null,
                'quantity' => (float) $bagItem['quantity'],
                'formattedQuantity' => $bagItem['formattedQuantity'] ?? $this->formatQuantity((float) $bagItem['quantity']),
                'pendingBaggedQuantity' => (float) $bagItem['pendingBaggedQuantity'],
                'unitAbbreviation' => $bagItem['unitAbbreviation'],
                'unitLabel' => $bagItem['unitLabel'],
                'deliveryDate' => $bagItem['deliveryDate'] ?? null,
            ],
            $bagItems,
        ));

        $this->modalConfirm = $this->bagItems !== [];
    }

    public function updatedMethod(string $method): void
    {
        $this->whatsappDeliveryError = null;

        if ($method === 'organizer') {
            $this->participant_whatsapp = '';
            $this->resetValidation('participant_whatsapp');
        }
    }

    public function submit(): void
    {
        if ($this->bagItems === []) {
            throw ValidationException::withMessages([
                'bagItems' => 'Adicione ao menos um item à sacola.',
            ]);
        }

        $this->whatsappDeliveryError = null;
        $this->participant_whatsapp = $this->normalizeWhatsapp($this->participant_whatsapp);
        $validated = $this->validate($this->submitRules());

        if ($this->participant_whatsapp !== '' && ! $this->isValidWhatsapp($this->participant_whatsapp)) {
            throw ValidationException::withMessages([
                'participant_whatsapp' => 'Informe um WhatsApp válido.',
            ]);
        }

        $confirmationCode = $validated['method'] === 'whatsapp'
            ? $this->generateConfirmationCode()
            : null;

        $bag = DB::transaction(function () use ($confirmationCode, $validated): Bag {
            $campaign = Campaign::query()->findOrFail($this->campaignId);
            $bagCode = $this->generateBagCode($campaign, $validated['participant_name']);

            $bag = Bag::query()->create([
                'campaign_id' => $campaign->id,
                'code' => $bagCode,
                'participant_name' => $validated['participant_name'],
                'participant_whatsapp' => $validated['participant_whatsapp'] ?: null,
                'confirmation_code' => $confirmationCode,
            ]);

            foreach ($this->bagItems as $bagItem) {
                $item = CampaignItem::query()
                    ->where('campaign_id', $campaign->id)
                    ->lockForUpdate()
                    ->findOrFail($bagItem['id']);

                $quantity = $this->validatedItemQuantity($item, $bagItem);

                $bag->items()->create([
                    'campaign_item_id' => $item->id,
                    'quantity' => $quantity,
                    'status' => BagItemStatusEnum::PENDING,
                ]);
            }

            return $bag;
        });

        $this->bagId = $bag->id;
        $this->code = $bag->code;
        $this->modalConfirm = false;

        if ($validated['method'] === 'organizer') {
            PublicCampaignBagSession::forget($this->campaignId);
            $this->flashBagFinish($bag, 'organizer');
            $this->redirectRoute('public.campaigns.bag.finish', [$this->campaignId]);

            return;
        }

        try {
            $this->sendConfirmationCode($bag);
        } catch (\Throwable $exception) {
            $this->handleConfirmationCodeDeliveryFailure($bag, $exception);

            return;
        }

        $this->pinModal = true;
    }

    public function confirmPin(): void
    {
        $this->validateOnly('pin');
        $bag = Bag::query()
            ->where('campaign_id', $this->campaignId)
            ->whereKey($this->bagId)
            ->firstOrFail();

        if (! hash_equals((string) $bag->confirmation_code, $this->pin)) {
            throw ValidationException::withMessages([
                'pin' => 'O código informado está incorreto.',
            ]);
        }

        DB::transaction(function () use ($bag): void {
            $bag->update([
                'confirmed_by' => 'participant',
                'confirmed_at' => now(),
                'confirmation_code' => null,
            ]);

            $bag->items()->update([
                'status' => BagItemStatusEnum::CONFIRMED->value,
            ]);

            $this->refreshBaggedQuantities($bag);
        });

        PublicCampaignBagSession::forget($this->campaignId);
        $this->flashBagFinish($bag, 'whatsapp');
        $this->redirectRoute('public.campaigns.bag.finish', [$this->campaignId]);
    }

    public function rules(): array
    {
        return array_merge($this->submitRules(), [
            'pin' => [
                'required',
                'digits:5',
            ],
        ]);
    }

    private function submitRules(): array
    {
        return [
            'participant_name' => [
                'required',
                'string',
                'max:255',
            ],
            'method' => [
                'required',
                'in:whatsapp,organizer',
            ],
            'participant_whatsapp' => [
                'required_if:method,whatsapp',
                'nullable',
                'digits_between:10,11',
            ],
        ];
    }

    public function validationAttributes(): array
    {
        return [
            'participant_name' => 'nome',
            'method' => 'forma de confirmação',
            'participant_whatsapp' => 'WhatsApp',
            'pin' => 'código',
        ];
    }

    #[Computed]
    public function totalItems(): int
    {
        return count($this->bagItems);
    }

    /**
     * @param  array{id: int, name: string, complement: ?string, quantity: float, formattedQuantity: string, pendingBaggedQuantity: float, unitAbbreviation: string, unitLabel: string, deliveryDate: ?string}  $bagItem
     */
    private function validatedItemQuantity(CampaignItem $item, array $bagItem): float
    {
        $quantity = (float) $bagItem['quantity'];
        $pendingQuantity = max((float) $item->required_quantity - (float) $item->bagged_quantity, 0);

        if ($quantity < 0.1 || $quantity > $pendingQuantity) {
            throw ValidationException::withMessages([
                'bagItems' => "A quantidade de {$item->name} não está mais disponível.",
            ]);
        }

        return $quantity;
    }

    private function refreshBaggedQuantities(Bag $bag): void
    {
        $bag->items()
            ->pluck('campaign_item_id')
            ->unique()
            ->each(function (int $campaignItemId): void {
                $item = CampaignItem::query()
                    ->where('campaign_id', $this->campaignId)
                    ->lockForUpdate()
                    ->findOrFail($campaignItemId);

                $this->refreshBaggedQuantity($item);
            });
    }

    private function refreshBaggedQuantity(CampaignItem $item): void
    {
        $baggedQuantity = $item->bagItems()
            ->whereIn('status', [
                BagItemStatusEnum::CONFIRMED->value,
                BagItemStatusEnum::RECEIVED->value,
            ])
            ->sum('quantity');

        $item->update([
            'bagged_quantity' => $baggedQuantity,
        ]);
    }

    private function normalizeWhatsapp(string $whatsapp): string
    {
        return preg_replace('/\D/', '', $whatsapp) ?? '';
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

    private function generateBagCode(Campaign $campaign, string $participantName): string
    {
        $generateBagCodeService = app(GenerateBagCodeService::class);

        do {
            $code = $generateBagCodeService->generateUniqueCode($participantName, $campaign->name);
        } while (
            Bag::query()
                ->where('campaign_id', $campaign->id)
                ->where('code', $code)
                ->exists()
        );

        return $code;
    }

    private function sendConfirmationCode(Bag $bag): void
    {
        app(SendBagConfirmationCodeService::class)->send($bag);
    }

    private function handleConfirmationCodeDeliveryFailure(Bag $bag, \Throwable $exception): void
    {
        $this->logConfirmationCodeDeliveryFailure($bag, $exception);

        DB::transaction(function () use ($bag): void {
            $bag->items()->delete();
            $bag->forceDelete();
        });

        $this->reset(['participant_whatsapp', 'pin', 'bagId', 'code']);

        $this->method = 'organizer';
        $this->modalConfirm = true;
        $this->pinModal = false;
        $this->whatsappDeliveryError = 'Não conseguimos enviar o código pelo WhatsApp agora. Para continuar, sua sacola ficará para confirmação do organizador.';
    }

    private function logConfirmationCodeDeliveryFailure(Bag $bag, \Throwable $exception): void
    {
        $response = $exception instanceof RequestException
            ? $exception->response
            : null;

        Log::error('Failed to send public bag confirmation code through Evolution API.', [
            'bag_id' => $bag->id,
            'campaign_id' => $bag->campaign_id,
            'participant_whatsapp' => $bag->participant_whatsapp,
            'evolution_instance' => config('services.evolution.instance'),
            'exception' => get_class($exception),
            'message' => $exception->getMessage(),
            'status' => $response?->status(),
            'response' => $response?->json() ?? $response?->body(),
        ]);
    }

    private function flashBagFinish(Bag $bag, string $method): void
    {
        session()->flash('bag_finish', [
            'method' => $method,
            'campaign_name' => $bag->campaign->name,
            'participant_name' => $bag->participant_name,
            'bag_code' => $bag->code,
        ]);
    }

    private function resetForm(): void
    {
        $this->reset([
            'bagItems',
            'modalConfirm',
            'pinModal',
            'participant_name',
            'participant_whatsapp',
            'method',
            'pin',
            'bagId',
            'code',
            'whatsappDeliveryError',
        ]);

        $this->method = 'whatsapp';
        $this->resetValidation();
    }

    private function formatQuantity(float $quantity): string
    {
        if (floor($quantity) === $quantity) {
            return (string) (int) $quantity;
        }

        return number_format($quantity, 1, ',', '');
    }
};
