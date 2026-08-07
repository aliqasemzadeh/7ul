<?php

use App\Enums\LinkTypeEnum;
use App\Models\Link;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('components.layouts.base', [
    'lang' => 'fa',
    'dir' => 'rtl',
    'class' => 'bg-bg text-fg min-h-screen font-sans antialiased',
])] class extends Component
{
    public string $destination = '';

    public string $type = LinkTypeEnum::Link->value;

    public bool $isPublic = true;

    public ?string $shortLink = null;

    public ?string $shortCode = null;

    public function mount(): void
    {
        app()->setLocale('fa');
    }

    public function rendering($view): void
    {
        $view->title(__('app.shortener.title'));
    }

    public function generateShortLink(): void
    {
        $validated = $this->validate([
            'destination' => ['required', 'string'],
            'type' => ['required', Rule::enum(LinkTypeEnum::class)],
            'isPublic' => ['boolean'],
        ], [
            'destination.required' => __('app.shortener.destination_required'),
        ]);

        do {
            $code = Str::random(8);
        } while (Link::query()->where('short_code', $code)->exists());

        $link = Link::query()->create([
            'user_id' => Auth::id(),
            'destination' => $validated['destination'],
            'short_code' => $code,
            'type' => $validated['type'],
            'creator_ip' => request()->ip(),
            'is_public_stats' => $validated['isPublic'],
        ]);

        $this->shortCode = $link->short_code;
        $this->shortLink = url('/'.$link->short_code);
    }
};
?>

<div class="relative flex min-h-screen flex-col overflow-hidden">
    <div
        aria-hidden="true"
        class="pointer-events-none absolute inset-0 -z-10 ui-radial-gradient text-primary/25 [--unify-radial-bg:var(--color-bg)]"
    ></div>

    <header class="mx-auto flex w-full max-w-7xl items-center justify-between gap-4 px-5 py-5 sm:px-8 lg:px-10">
        <a href="{{ route('home') }}" class="group flex items-center gap-3" wire:navigate>
            <span
                class="flex size-10 items-center justify-center rounded-ui bg-primary text-lg font-black text-white shadow-sm transition duration-300 ease-out group-hover:scale-105"
            >
                {{ __('app.welcome.brand_short') }}
            </span>
            <span class="hidden text-xl font-black tracking-tight text-fg-title sm:inline">
                Seven Up
                <span class="text-primary">{{ __('app.welcome.brand_accent') }}</span>
            </span>
        </a>

        <div class="flex items-center gap-2">
            <x-ui.theme-toggle />
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <x-ui.button type="submit" variant="ghost" size="sm">
                    {{ __('app.shortener.logout') }}
                </x-ui.button>
            </form>
        </div>
    </header>

    <main class="mx-auto flex w-full max-w-xl flex-1 flex-col px-5 pb-16 pt-6 sm:px-8">
        <div class="animate-[modal-animation-in_0.5s_ease-out]">
            <p class="mb-3 text-sm font-semibold tracking-wide text-primary">
                {{ __('app.welcome.brand') }}
            </p>
            <h1 class="text-3xl font-black text-fg-title sm:text-4xl">
                {{ __('app.shortener.heading') }}
            </h1>
            <p class="mt-3 text-fg-muted">
                {{ __('app.shortener.subtitle') }}
            </p>

            <x-ui.card class="mt-8 p-(--card-padding)" :shadow="true">
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
                <x-ui.card class="mt-6 space-y-4 p-(--card-padding)" :shadow="true">
                    <div>
                        <p class="text-sm font-medium text-fg-muted">{{ __('app.shortener.your_link') }}</p>
                        <p class="mt-2 break-all font-semibold text-fg-title" dir="ltr" x-ref="shortLink">
                            {{ $shortLink }}
                        </p>
                    </div>

                    <div
                        class="flex flex-col gap-2 sm:flex-row"
                        x-data="{ copied: false }"
                    >
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
                            :href="route('links.stats', $shortCode)"
                            variant="outline"
                            size="md"
                            class="w-full justify-center sm:flex-1"
                        >
                            {{ __('app.shortener.view_stats') }}
                        </x-ui.button>
                    </div>
                </x-ui.card>
            @endif
        </div>
    </main>
</div>
