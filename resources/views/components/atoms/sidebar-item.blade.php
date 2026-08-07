@props(['text', 'icon', 'href' => '', 'isActive' => null])

<li
    class="relative before:absolute before:-end-0.5 before:inset-y-2.5 before:w-0.5 before:rounded-s-md before:bg-transparent has-fx-active:before:bg-fg-title"
>
    <a
        href="{{ $href }}"
        wire:navigate
        data-state="{{ $isActive ? 'active' : null }}"
        aria-label="{{ $text }}"
        aria-current="{{ $isActive ? 'page' : 'false' }}"
        {{ $attributes->class([
            'flex h-10 items-center gap-x-2.5 rounded-ui border border-transparent px-3 py-1.5 text-sm',
            'fx-active:border-bg-muted/70 fx-active:bg-bg fx-active:text-fg-title fx-active:shadow-xs',
            'fx-current:border-bg-muted/70 fx-current:bg-bg fx-current:text-fg-title fx-current:shadow-xs',
        ]) }}
    >
        <x-ui.icon size="xs" :name="$icon" />
        {{ $text }}
    </a>
</li>
