<?php

use App\Actions\Links\CreateShortLink;
use App\Actions\Links\GenerateLinkQrCode;
use App\Enums\LinkTypeEnum;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('components.layouts.user')] class extends Component
{
    public string $destination = '';

    public string $type = LinkTypeEnum::Link->value;

    public bool $isPublic = true;

    public ?string $shortLink = null;

    public ?string $shortCode = null;

    public ?string $qrCodeDataUri = null;

    public function rendering($view): void
    {
        $view->title(__('app.panel.create.title'));
    }

    public function generateShortLink(CreateShortLink $createShortLink, GenerateLinkQrCode $generateLinkQrCode): void
    {
        $validated = $this->validate([
            'destination' => ['required', 'string'],
            'type' => ['required', Rule::enum(LinkTypeEnum::class)],
            'isPublic' => ['boolean'],
        ], [
            'destination.required' => __('app.shortener.destination_required'),
        ]);

        $link = $createShortLink->handle(
            user: Auth::user(),
            destination: $validated['destination'],
            type: LinkTypeEnum::from($validated['type']),
            isPublicStats: $validated['isPublic'],
            creatorIp: request()->ip(),
        );

        $this->shortCode = $link->short_code;
        $this->shortLink = url('/'.$link->short_code);
        $this->qrCodeDataUri = $generateLinkQrCode->handle($this->shortLink);
    }
};
?>

<div class="mx-auto max-w-xl space-y-6">
    <div>
        <h2 class="text-2xl font-black text-fg-title">{{ __('app.panel.create.heading') }}</h2>
        <p class="mt-1 text-fg-muted">{{ __('app.panel.create.subtitle') }}</p>
    </div>

    <x-ui.card class="p-(--card-padding)" :shadow="true">
        <form wire:submit="generateShortLink" class="space-y-5">
            <div>
                <x-ui.select
                    name="type"
                    :label="__('app.shortener.type')"
                    wire:model="type"
                    :invalid="$errors->has('type')"
                    class="w-full"
                >
                    @foreach (LinkTypeEnum::cases() as $case)
                        <option value="{{ $case->value }}" wire:key="type-{{ $case->value }}">
                            {{ $case->label() }}
                        </option>
                    @endforeach
                </x-ui.select>
                @error('type')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <x-ui.textarea
                    name="destination"
                    :label="__('app.shortener.destination')"
                    :placeholder="__('app.shortener.destination_placeholder')"
                    wire:model="destination"
                    :invalid="$errors->has('destination')"
                    class="w-full min-h-32"
                />
                @error('destination')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <x-ui.checkbox
                id="isPublic"
                name="isPublic"
                value="1"
                :label="__('app.shortener.public_stats')"
                wire:model="isPublic"
            />

            <x-ui.button type="submit" size="lg" class="w-full justify-center">
                <span wire:loading.remove wire:target="generateShortLink">
                    {{ __('app.shortener.generate') }}
                </span>
                <span wire:loading wire:target="generateShortLink">...</span>
            </x-ui.button>
        </form>
    </x-ui.card>

    @if ($shortLink)
        <x-ui.card class="space-y-4 p-(--card-padding)" :shadow="true">
            <div>
                <p class="text-sm font-medium text-fg-muted">{{ __('app.shortener.your_link') }}</p>
                <p class="mt-2 break-all font-semibold text-fg-title" dir="ltr" x-ref="shortLink">
                    {{ $shortLink }}
                </p>
            </div>

            @if ($qrCodeDataUri)
                <div class="flex flex-col items-center gap-3 border-t border-border pt-4">
                    <p class="text-sm font-medium text-fg-muted">{{ __('app.shortener.qr_label') }}</p>
                    <img
                        src="{{ $qrCodeDataUri }}"
                        alt="{{ __('app.shortener.qr_label') }}"
                        class="size-48 rounded-ui bg-white p-2"
                        width="192"
                        height="192"
                    />
                    <x-ui.button
                        :href="$qrCodeDataUri"
                        download="7ul-{{ $shortCode }}.svg"
                        variant="outline"
                        size="md"
                        class="w-full justify-center sm:w-auto"
                        :in-same-window="true"
                    >
                        {{ __('app.shortener.download_qr') }}
                    </x-ui.button>
                </div>
            @endif

            <div class="flex flex-col gap-2 sm:flex-row" x-data="{ copied: false }">
                <x-ui.button
                    type="button"
                    size="md"
                    class="w-full justify-center sm:flex-1"
                    x-on:click="
                        navigator.clipboard.writeText($refs.shortLink.innerText.trim());
                        copied = true;
                        setTimeout(() => copied = false, 2000);
                    "
                >
                    <span x-show="!copied">{{ __('app.shortener.copy') }}</span>
                    <span x-cloak x-show="copied">{{ __('app.shortener.copied') }}</span>
                </x-ui.button>

                <x-ui.button
                    :href="route('user.links.stats', $shortCode)"
                    variant="outline"
                    size="md"
                    class="w-full justify-center sm:flex-1"
                    wire:navigate
                >
                    {{ __('app.shortener.view_stats') }}
                </x-ui.button>
            </div>
        </x-ui.card>
    @endif
</div>
