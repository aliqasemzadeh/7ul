<?php

use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('components.layouts.admin')] class extends Component
{
    public function rendering($view): void
    {
        $view->title(__('app.admin.nav.settings').' | 7UL.ir');
    }
};
?>

<div class="space-y-2">
    <h2 class="text-2xl font-black text-fg-title">{{ __('app.admin.nav.settings') }}</h2>
</div>
