<?php

use App\Actions\Links\CreateShortLink;
use App\Enums\LinkTypeEnum;
use App\Livewire\Concerns\EnsuresUserIsAdmin;
use App\Livewire\Forms\Admin\LinkForm;
use App\Models\Link;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Morilog\Jalali\Jalalian;

new #[Layout('components.layouts.admin')] class extends Component
{
    use EnsuresUserIsAdmin;
    use WithPagination;

    public LinkForm $form;

    #[Url]
    public string $search = '';

    public function rendering($view): void
    {
        $view->title(__('app.admin.links.title'));
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function links()
    {
        return Link::query()
            ->with('user')
            ->withCount('visits')
            ->when($this->search !== '', function ($query): void {
                $term = '%'.$this->search.'%';
                $query->where(function ($inner) use ($term): void {
                    $inner->where('short_code', 'like', $term)
                        ->orWhere('destination', 'like', $term)
                        ->orWhereHas('user', function ($userQuery) use ($term): void {
                            $userQuery->where('mobile', 'like', $term);
                        });
                });
            })
            ->latest()
            ->paginate(10);
    }

    #[Computed]
    public function users()
    {
        return User::query()
            ->orderBy('mobile')
            ->get(['id', 'mobile']);
    }

    public function create(): void
    {
        $this->form->resetForm();
        $this->openFormSheet();
    }

    public function edit(int $linkId): void
    {
        $link = Link::query()->findOrFail($linkId);
        $this->form->setLink($link);
        $this->openFormSheet();
    }

    public function save(CreateShortLink $createShortLink): void
    {
        if ($this->form->link) {
            $this->form->update();
            $message = __('app.admin.links.updated');
        } else {
            $this->form->store($createShortLink);
            $message = __('app.admin.links.created');
        }

        $this->closeFormSheet();
        $this->form->resetForm();
        unset($this->links);

        $this->dispatch('notify', message: $message, type: 'success');
    }

    public function delete(int $linkId): void
    {
        $link = Link::query()->findOrFail($linkId);
        $link->delete();

        unset($this->links);

        $this->dispatch('notify', message: __('app.admin.links.deleted'), type: 'success');
    }

    public function formatJalali(?\Illuminate\Support\Carbon $date): string
    {
        if ($date === null) {
            return __('app.shortener.unknown');
        }

        return Jalalian::fromCarbon($date)->format('Y/m/d H:i');
    }

    protected function openFormSheet(): void
    {
        $this->js("document.dispatchEvent(new CustomEvent('sheet:admin-link-form:open'))");
    }

    protected function closeFormSheet(): void
    {
        $this->js("document.dispatchEvent(new CustomEvent('sheet:admin-link-form:close'))");
    }
};
?>

<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h2 class="text-2xl font-black text-fg-title">{{ __('app.admin.links.heading') }}</h2>
            <p class="mt-1 text-fg-muted">{{ __('app.admin.links.subtitle') }}</p>
        </div>

        <x-ui.button type="button" size="md" class="justify-center" wire:click="create">
            <span aria-hidden="true" class="iconify icon-[hugeicons--add-01] size-4"></span>
            {{ __('app.admin.links.create') }}
        </x-ui.button>
    </div>

    <x-ui.card class="p-(--card-padding)" :shadow="true">
        <x-ui.input
            name="search"
            :label="__('app.admin.links.search')"
            :placeholder="__('app.admin.links.search_placeholder')"
            wire:model.live.debounce.300ms="search"
            class="w-full"
        />
    </x-ui.card>

    <x-ui.card class="overflow-hidden p-0" :shadow="true">
        <x-ui.table striped hoverable>
            <x-ui.table.columns wrapper="bg-bg-subtle border-b border-border text-fg-muted [--gutter-x:--spacing(4)] [--gutter-y:--spacing(3)]">
                <x-ui.table.column class="!text-start">{{ __('app.admin.links.short_link') }}</x-ui.table.column>
                <x-ui.table.column class="!text-start">{{ __('app.admin.links.type') }}</x-ui.table.column>
                <x-ui.table.column class="!text-start">{{ __('app.admin.links.destination') }}</x-ui.table.column>
                <x-ui.table.column class="!text-start">{{ __('app.admin.links.owner') }}</x-ui.table.column>
                <x-ui.table.column class="!text-start">{{ __('app.admin.links.visits') }}</x-ui.table.column>
                <x-ui.table.column class="!text-start">{{ __('app.admin.links.created_at') }}</x-ui.table.column>
                <x-ui.table.column class="!text-start">{{ __('app.admin.links.actions') }}</x-ui.table.column>
            </x-ui.table.columns>

            <x-ui.table.rows class="[--gutter-x:--spacing(4)] [--gutter-y:--spacing(3)]">
                @forelse ($this->links as $link)
                    <x-ui.table.row wire:key="admin-link-{{ $link->id }}">
                        <x-ui.table.cell class="!text-start font-semibold text-fg-title" dir="ltr">
                            {{ url('/'.$link->short_code) }}
                        </x-ui.table.cell>
                        <x-ui.table.cell class="!text-start">
                            {{ $link->type->label() }}
                        </x-ui.table.cell>
                        <x-ui.table.cell
                            class="!text-start max-w-56 truncate text-fg-muted"
                            title="{{ $link->destination }}"
                        >
                            {{ $link->destination }}
                        </x-ui.table.cell>
                        <x-ui.table.cell class="!text-start font-semibold" dir="ltr">
                            {{ $link->user?->mobile ?: __('app.shortener.unknown') }}
                        </x-ui.table.cell>
                        <x-ui.table.cell class="!text-start font-semibold">
                            {{ number_format($link->visits_count) }}
                        </x-ui.table.cell>
                        <x-ui.table.cell class="!text-start" dir="ltr">
                            {{ $this->formatJalali($link->created_at) }}
                        </x-ui.table.cell>
                        <x-ui.table.cell class="!text-start" white-space="normal">
                            <div class="flex flex-wrap gap-2">
                                <x-ui.button
                                    type="button"
                                    size="sm"
                                    variant="outline"
                                    title="{{ __('app.admin.links.edit') }}"
                                    wire:click="edit({{ $link->id }})"
                                >
                                    <span aria-hidden="true" class="iconify icon-[hugeicons--edit-02] size-4"></span>
                                    <span class="sr-only">{{ __('app.admin.links.edit') }}</span>
                                </x-ui.button>

                                <x-ui.button
                                    type="button"
                                    size="sm"
                                    variant="ghost"
                                    intent="danger"
                                    title="{{ __('app.admin.links.delete') }}"
                                    wire:click="delete({{ $link->id }})"
                                    wire:confirm="{{ __('app.admin.links.confirm_delete') }}"
                                >
                                    <span aria-hidden="true" class="iconify icon-[hugeicons--delete-02] size-4"></span>
                                    <span class="sr-only">{{ __('app.admin.links.delete') }}</span>
                                </x-ui.button>
                            </div>
                        </x-ui.table.cell>
                    </x-ui.table.row>
                @empty
                    <x-ui.table.row>
                        <x-ui.table.cell
                            colspan="7"
                            white-space="normal"
                            align="center"
                            class="py-10 text-fg-muted"
                        >
                            {{ __('app.admin.links.empty') }}
                        </x-ui.table.cell>
                    </x-ui.table.row>
                @endforelse
            </x-ui.table.rows>
        </x-ui.table>

        @if ($this->links->hasPages())
            <div class="border-t border-border px-4 py-3">
                {{ $this->links->links() }}
            </div>
        @endif
    </x-ui.card>

    <x-ui.slideover id="admin-link-form" position="right" size="md">
        <x-ui.slideover.content>
            <x-ui.slideover.header>
                <x-ui.slideover.title>
                    {{ $form->link ? __('app.admin.links.edit_heading') : __('app.admin.links.create_heading') }}
                </x-ui.slideover.title>
            </x-ui.slideover.header>

            <form wire:submit="save" class="flex size-full flex-col">
                <x-ui.slideover.body>
                    <div class="space-y-4">
                        @if ($form->link)
                            <div>
                                <p class="text-sm font-medium text-fg-muted">{{ __('app.admin.links.short_link') }}</p>
                                <p class="mt-1 break-all font-semibold text-fg-title" dir="ltr">
                                    {{ url('/'.$form->link->short_code) }}
                                </p>
                            </div>
                        @endif

                        <div>
                            <x-ui.select
                                name="user_id"
                                :label="__('app.admin.links.owner')"
                                wire:model="form.user_id"
                                :invalid="$errors->has('form.user_id')"
                                class="w-full"
                            >
                                <option value="">{{ __('app.admin.links.owner_placeholder') }}</option>
                                @foreach ($this->users as $user)
                                    <option value="{{ $user->id }}" wire:key="owner-{{ $user->id }}">
                                        {{ $user->mobile }}
                                    </option>
                                @endforeach
                            </x-ui.select>
                            @error('form.user_id')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <x-ui.select
                                name="type"
                                :label="__('app.admin.links.type')"
                                wire:model="form.type"
                                :invalid="$errors->has('form.type')"
                                class="w-full"
                            >
                                @foreach (LinkTypeEnum::cases() as $case)
                                    <option value="{{ $case->value }}" wire:key="type-{{ $case->value }}">
                                        {{ $case->label() }}
                                    </option>
                                @endforeach
                            </x-ui.select>
                            @error('form.type')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <x-ui.textarea
                                name="destination"
                                :label="__('app.admin.links.destination')"
                                :placeholder="__('app.shortener.destination_placeholder')"
                                wire:model="form.destination"
                                :invalid="$errors->has('form.destination')"
                                class="w-full min-h-32"
                            />
                            @error('form.destination')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <x-ui.checkbox
                            id="is_public_stats"
                            name="is_public_stats"
                            value="1"
                            :label="__('app.admin.links.public_stats')"
                            wire:model="form.is_public_stats"
                        />
                    </div>
                </x-ui.slideover.body>

                <x-ui.slideover.footer justify="end">
                    <x-ui.button type="submit" size="md" class="w-full justify-center">
                        <span wire:loading.remove wire:target="save">
                            {{ __('app.admin.links.save') }}
                        </span>
                        <span wire:loading wire:target="save">...</span>
                    </x-ui.button>
                </x-ui.slideover.footer>
            </form>
        </x-ui.slideover.content>
    </x-ui.slideover>
</div>
