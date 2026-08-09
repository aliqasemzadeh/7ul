<?php

use Livewire\Component;
use App\Jobs\System\RunBackupJob;
use Illuminate\Support\Facades\Artisan;

new class extends Component
{
    public function runBackup()
    {
        RunBackupJob::dispatch('database', 'local');
        session()->flash('status', 'پشتیبان‌گیری در صف اجرا قرار گرفت.');
    }
};
?>

<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">پشتیبان‌گیری سیستم</h1>
        <button wire:click="runBackup()" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
            اجرای دستی پشتیبان‌گیری دیتابیس
        </button>
    </div>

    @if (session('status'))
        <div class="p-4 mb-4 text-green-700 bg-green-100 rounded">
            {{ session('status') }}
        </div>
    @endif

    <div class="bg-white shadow rounded-lg p-6">
        <h2 class="text-lg font-semibold mb-4">تنظیمات فعلی</h2>
        <ul class="space-y-2">
            <li><strong>زمان‌بندی:</strong> هر روز ساعت ۰۶:۰۰</li>
            <li><strong>نوع پشتیبان:</strong> فقط دیتابیس</li>
            <li><strong>محل ذخیره:</strong> محلی (storage/app/private)</li>
        </ul>
    </div>
</div>
