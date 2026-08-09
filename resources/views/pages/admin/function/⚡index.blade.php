<?php

use App\Livewire\Concerns\EnsuresUserIsAdmin;
use App\Models\SystemActionLog;
use App\Jobs\System\RunArtisanCommandJob;
use App\Jobs\System\UpdateProjectJob;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('components.layouts.admin')] class extends Component
{
    use EnsuresUserIsAdmin;

    public string $customCommand = '';

    public function rendering($view): void
    {
        $view->title(__('app.admin.nav.functions') . ' | 7UL.ir');
    }

    public function runCommand(string $command): void
    {
        dispatch(new RunArtisanCommandJob($command));
        $this->dispatch('notify', message: 'دستور در صف اجرا قرار گرفت.', type: 'success');
    }

    public function updateProject(bool $withComposer = false): void
    {
        dispatch(new UpdateProjectJob($withComposer));
        $this->dispatch('notify', message: 'بروزرسانی پروژه در صف اجرا قرار گرفت.', type: 'success');
    }

    public function runCustomCommand(): void
    {
        $this->validate([
            'customCommand' => 'required|string|starts_with:php artisan ,artisan ',
        ]);

        $command = str_replace(['php artisan ', 'artisan '], '', $this->customCommand);

        // Basic safety check
        if (str_contains($command, ';') || str_contains($command, '&') || str_contains($command, '|')) {
            $this->dispatch('notify', message: 'دستور نامعتبر است.', type: 'danger');
            return;
        }

        dispatch(new RunArtisanCommandJob($command));
        $this->customCommand = '';
        $this->dispatch('notify', message: 'دستور سفارشی در صف اجرا قرار گرفت.', type: 'success');
    }

    public function with(): array
    {
        return [
            'logs' => SystemActionLog::latest()->take(10)->get(),
        ];
    }
};
?>

<div class="space-y-6" wire:poll.3s>
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-black text-fg-title">{{ __('app.admin.nav.functions') }}</h2>
    </div>

    {{-- Main Functions --}}
    <x-ui.card class="p-6">
        <div class="mb-4">
            <h3 class="text-lg font-bold text-fg-title">توابع اصلی</h3>
            <p class="text-sm text-fg-muted">پاکسازی کش، روت، ویو و کانفیگ لاراولی</p>
        </div>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <x-ui.button variant="soft" intent="primary" class="h-20 flex-col gap-2" wire:click="runCommand('view:clear')">
                <span class="iconify icon-[hugeicons--file-code] size-6"></span>
                <span>view:clear</span>
            </x-ui.button>
            <x-ui.button variant="soft" intent="secondary" class="h-20 flex-col gap-2" wire:click="runCommand('cache:clear')">
                <span class="iconify icon-[hugeicons--database] size-6"></span>
                <span>cache:clear</span>
            </x-ui.button>
            <x-ui.button variant="soft" intent="info" class="h-20 flex-col gap-2" wire:click="runCommand('route:clear')">
                <span class="iconify icon-[hugeicons--route-01] size-6"></span>
                <span>route:clear</span>
            </x-ui.button>
            <x-ui.button variant="soft" intent="warning" class="h-20 flex-col gap-2" wire:click="runCommand('config:clear')">
                <span class="iconify icon-[hugeicons--settings-03] size-6"></span>
                <span>config:clear</span>
            </x-ui.button>
        </div>
    </x-ui.card>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Project Update --}}
        <x-ui.card class="p-6">
            <div class="mb-4">
                <h3 class="text-lg font-bold text-fg-title">بروزرسانی پروژه</h3>
                <p class="text-sm text-fg-muted">دریافت آخرین تغییرات از Git و اجرای عملیات تکمیلی</p>
            </div>
            <div class="flex flex-col gap-4">
                <x-ui.button variant="solid" intent="primary" class="w-full justify-between" wire:click="updateProject(true)">
                    <div class="flex items-center gap-3">
                        <span class="iconify icon-[hugeicons--refresh] size-5"></span>
                        <div class="text-start">
                            <div class="font-bold">بروزرسانی کامل</div>
                            <div class="text-2xs opacity-80">همراه با composer update و ساخت Assetها</div>
                        </div>
                    </div>
                    <span class="iconify icon-[hugeicons--arrow-right-01] size-5"></span>
                </x-ui.button>

                <x-ui.button variant="outline" intent="primary" class="w-full justify-between" wire:click="updateProject(false)">
                    <div class="flex items-center gap-3">
                        <span class="iconify icon-[hugeicons--flash] size-5"></span>
                        <div class="text-start">
                            <div class="font-bold">بروزرسانی سریع</div>
                            <div class="text-2xs opacity-80">بدون تغییر در وابستگی‌ها (فقط git pull و migrate)</div>
                        </div>
                    </div>
                    <span class="iconify icon-[hugeicons--arrow-right-01] size-5"></span>
                </x-ui.button>
            </div>
        </x-ui.card>

        {{-- Custom Artisan Command --}}
        <x-ui.card class="p-6">
            <div class="mb-4">
                <h3 class="text-lg font-bold text-fg-title">خط فرمان Artisan</h3>
                <p class="text-sm text-fg-muted">اجرای هرگونه دستور php artisan در محیط سرور</p>
            </div>
            <form wire:submit="runCustomCommand" class="space-y-4">
                <x-ui.input.group label="دستور آرتیسان" help="مثال: php artisan migrate --seed">
                    <x-ui.input
                        dir="ltr"
                        placeholder="php artisan ..."
                        wire:model="customCommand"
                    />
                </x-ui.input.group>
                <x-ui.button type="submit" variant="solid" intent="gray" class="w-full gap-2">
                    <span class="iconify icon-[hugeicons--terminal] size-5"></span>
                    <span>اجرای دستور</span>
                </x-ui.button>
            </form>
        </x-ui.card>
    </div>

    {{-- Execution Logs --}}
    <x-ui.card class="overflow-hidden">
        <div class="border-b border-border bg-bg-muted/30 px-6 py-4">
            <h3 class="font-bold text-fg-title text-lg">تاریخچه عملیات</h3>
        </div>
        <div class="divide-y divide-border">
            @forelse($logs as $log)
                <div class="p-6 space-y-4" x-data="{ expanded: false }">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div @class([
                                'size-2.5 rounded-full animate-pulse' => $log->status === 'running',
                                'size-2.5 rounded-full bg-success' => $log->status === 'success',
                                'size-2.5 rounded-full bg-danger' => $log->status === 'failed',
                            ])></div>
                            <span class="font-mono text-sm font-bold text-fg-title">{{ $log->command }}</span>
                            <span class="text-2xs text-fg-muted">{{ $log->created_at->diffForHumans() }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            @if($log->status === 'running')
                                <span class="text-xs text-primary font-medium">در حال اجرا...</span>
                            @else
                                <x-ui.button variant="ghost" size="xs" @click="expanded = !expanded">
                                    <span x-text="expanded ? 'بستن خروجی' : 'مشاهده خروجی'"></span>
                                </x-ui.button>
                            @endif
                        </div>
                    </div>

                    @if($log->output)
                        <div x-show="expanded" x-cloak x-collapse>
                            <pre class="overflow-x-auto rounded-lg bg-black p-4 font-mono text-xs text-green-400 ltr shadow-inner"><code>{{ $log->output }}</code></pre>
                        </div>
                    @endif
                </div>
            @empty
                <div class="p-10 text-center text-fg-muted">
                    <span class="iconify icon-[hugeicons--folder-open] mx-auto mb-2 size-10 opacity-20"></span>
                    <p>هنوز عملیاتی ثبت نشده است.</p>
                </div>
            @endforelse
        </div>
    </x-ui.card>
</div>
