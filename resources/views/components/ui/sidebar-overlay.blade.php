@props(['blured' => true, 'appearance' => 'default'])

<div data-sidebar-overlay
    {{ $attributes->class([
        'fixed inset-0',
        'backdrop-blur-sm' => $blured && $appearance === 'default',
        'z-40 hidden bg-gray-800/40 fx-open:flex md:fx-open:hidden' => $appearance === 'default',
    ]) }}>
</div>
