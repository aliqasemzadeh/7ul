<?php

use App\Livewire\Forms\Admin\UserForm;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Morilog\Jalali\Jalalian;

new #[Layout('components.layouts.admin')] class extends Component
{
    use WithPagination;

    public UserForm $form;

    #[Url]
    public string $search = '';

    public function rendering($view): void
    {
        $view->title(__('app.admin.users.title'));
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function users()
    {
        return User::query()
            ->withCount('links')
            ->when($this->search !== '', function ($query): void {
                $term = '%'.$this->search.'%';
                $query->where(function ($inner) use ($term): void {
                    $inner->where('mobile', 'like', $term)
                        ->orWhere('registration_ip', 'like', $term);
                });
            })
            ->latest()
            ->paginate(10);
    }

    public function create(): void
    {
        $this->form->resetForm();
        $this->openFormSheet();
    }

    public function edit(int $userId): void
    {
        $user = User::query()->findOrFail($userId);
        $this->form->setUser($user);
        $this->openFormSheet();
    }

    public function save(): void
    {
        if ($this->form->user) {
            $this->form->update();
            $message = __('app.admin.users.updated');
        } else {
            $this->form->store();
            $message = __('app.admin.users.created');
        }

        $this->closeFormSheet();
        $this->form->resetForm();
        unset($this->users);

        $this->dispatch('notify', message: $message, type: 'success');
    }

    public function delete(int $userId): void
    {
        $user = User::query()->findOrFail($userId);

        if ($user->id === Auth::id()) {
            $this->dispatch('notify', message: __('app.admin.users.cannot_delete_self'), type: 'danger');

            return;
        }

        $user->delete();

        unset($this->users);

        $this->dispatch('notify', message: __('app.admin.users.deleted'), type: 'success');
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
        $this->js("document.dispatchEvent(new CustomEvent('sheet:admin-user-form:open'))");
    }

    protected function closeFormSheet(): void
    {
        $this->js("document.dispatchEvent(new CustomEvent('sheet:admin-user-form:close'))");
    }
};
?>

<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h2 class="text-2xl font-black text-fg-title">{{ __('app.admin.users.heading') }}</h2>
            <p class="mt-1 text-fg-muted">{{ __('app.admin.users.subtitle') }}</p>
        </div>

        <x-ui.button type="button" size="md" class="justify-center" wire:click="create">
            <span aria-hidden="true" class="iconify icon-[hugeicons--add-01] size-4"></span>
            {{ __('app.admin.users.create') }}
        </x-ui.button>
    </div>

    <x-ui.card class="p-(--card-padding)" :shadow="true">
        <x-ui.input
            name="search"
            :label="__('app.admin.users.search')"
            :placeholder="__('app.admin.users.search_placeholder')"
            wire:model.live.debounce.300ms="search"
            class="w-full"
        />
    </x-ui.card>

    <x-ui.card class="overflow-hidden p-0" :shadow="true">
        <x-ui.table striped hoverable>
            <x-ui.table.columns wrapper="bg-bg-subtle border-b border-border text-fg-muted [--gutter-x:--spacing(4)] [--gutter-y:--spacing(3)]">
                <x-ui.table.column class="!text-start">{{ __('app.admin.users.mobile') }}</x-ui.table.column>
                <x-ui.table.column class="!text-start">{{ __('app.admin.users.registration_ip') }}</x-ui.table.column>
                <x-ui.table.column class="!text-start">{{ __('app.admin.users.links_count') }}</x-ui.table.column>
                <x-ui.table.column class="!text-start">{{ __('app.admin.users.created_at') }}</x-ui.table.column>
                <x-ui.table.column class="!text-start">{{ __('app.admin.users.actions') }}</x-ui.table.column>
            </x-ui.table.columns>

            <x-ui.table.rows class="[--gutter-x:--spacing(4)] [--gutter-y:--spacing(3)]">
                @forelse ($this->users as $user)
                    <x-ui.table.row wire:key="admin-user-{{ $user->id }}">
                        <x-ui.table.cell class="!text-start font-semibold text-fg-title" dir="ltr">
                            {{ $user->mobile }}
                        </x-ui.table.cell>
                        <x-ui.table.cell class="!text-start text-fg-muted" dir="ltr">
                            {{ $user->registration_ip ?: __('app.shortener.unknown') }}
                        </x-ui.table.cell>
                        <x-ui.table.cell class="!text-start font-semibold">
                            {{ number_format($user->links_count) }}
                        </x-ui.table.cell>
                        <x-ui.table.cell class="!text-start" dir="ltr">
                            {{ $this->formatJalali($user->created_at) }}
                        </x-ui.table.cell>
                        <x-ui.table.cell class="!text-start" white-space="normal">
                            <div class="flex flex-wrap gap-2">
                                <x-ui.button
                                    type="button"
                                    size="sm"
                                    variant="outline"
                                    title="{{ __('app.admin.users.edit') }}"
                                    wire:click="edit({{ $user->id }})"
                                >
                                    <span aria-hidden="true" class="iconify icon-[hugeicons--edit-02] size-4"></span>
                                    <span class="sr-only">{{ __('app.admin.users.edit') }}</span>
                                </x-ui.button>

                                <x-ui.button
                                    type="button"
                                    size="sm"
                                    variant="outline"
                                    title="{{ __('app.admin.users.user_links') }}"
                                    :href="route('admin.users.links', $user)"
                                    wire:navigate
                                >
                                    <span aria-hidden="true" class="iconify icon-[hugeicons--link-02] size-4"></span>
                                    <span class="sr-only">{{ __('app.admin.users.user_links') }}</span>
                                </x-ui.button>

                                <x-ui.button
                                    type="button"
                                    size="sm"
                                    variant="outline"
                                    title="{{ __('app.admin.users.user_roles') }}"
                                    :href="route('admin.users.roles', $user)"
                                    wire:navigate
                                >
                                    <span aria-hidden="true" class="iconify icon-[hugeicons--user-shield-02] size-4"></span>
                                    <span class="sr-only">{{ __('app.admin.users.user_roles') }}</span>
                                </x-ui.button>

                                <x-ui.button
                                    type="button"
                                    size="sm"
                                    variant="ghost"
                                    intent="danger"
                                    title="{{ __('app.admin.users.delete') }}"
                                    wire:click="delete({{ $user->id }})"
                                    wire:confirm="{{ __('app.admin.users.confirm_delete') }}"
                                    :disabled="$user->id === auth()->id()"
                                >
                                    <span aria-hidden="true" class="iconify icon-[hugeicons--delete-02] size-4"></span>
                                    <span class="sr-only">{{ __('app.admin.users.delete') }}</span>
                                </x-ui.button>
                            </div>
                        </x-ui.table.cell>
                    </x-ui.table.row>
                @empty
                    <x-ui.table.row>
                        <x-ui.table.cell
                            colspan="5"
                            white-space="normal"
                            align="center"
                            class="py-10 text-fg-muted"
                        >
                            {{ __('app.admin.users.empty') }}
                        </x-ui.table.cell>
                    </x-ui.table.row>
                @endforelse
            </x-ui.table.rows>
        </x-ui.table>

        @if ($this->users->hasPages())
            <div class="border-t border-border px-4 py-3">
                {{ $this->users->links() }}
            </div>
        @endif
    </x-ui.card>

    <x-ui.slideover id="admin-user-form" position="right" size="md">
        <x-ui.slideover.content>
            <x-ui.slideover.header>
                <x-ui.slideover.title>
                    {{ $form->user ? __('app.admin.users.edit_heading') : __('app.admin.users.create_heading') }}
                </x-ui.slideover.title>
            </x-ui.slideover.header>

            <form wire:submit="save" class="flex size-full flex-col">
                <x-ui.slideover.body>
                    <div class="space-y-4">
                        <div>
                            <x-ui.input
                                name="mobile"
                                :label="__('app.admin.users.mobile')"
                                :placeholder="__('app.auth.mobile_placeholder')"
                                wire:model="form.mobile"
                                :invalid="$errors->has('form.mobile')"
                                class="w-full"
                                dir="ltr"
                            />
                            @error('form.mobile')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </x-ui.slideover.body>

                <x-ui.slideover.footer justify="end">
                    <x-ui.button type="submit" size="md" class="w-full justify-center">
                        <span wire:loading.remove wire:target="save">
                            {{ __('app.admin.users.save') }}
                        </span>
                        <span wire:loading wire:target="save">...</span>
                    </x-ui.button>
                </x-ui.slideover.footer>
            </form>
        </x-ui.slideover.content>
    </x-ui.slideover>
</div>
