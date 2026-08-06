<x-ui.button
    variant="ghost"
    size="sm"
    :iconOnly="true"
    x-on:click="$store.theme.toggle()"
    :aria-label="__('app.auth.toggle_theme')"
    class="relative"
>
    <span
        class="iconify icon-[hugeicons--sun-01] absolute start-1/2 top-1/2 size-5 -translate-x-1/2 -translate-y-1/2 duration-200 ease-linear invisible dark:visible"
        aria-hidden="true"
    ></span>
    <span
        class="iconify icon-[hugeicons--moon-02] absolute start-1/2 top-1/2 size-5 -translate-x-1/2 -translate-y-1/2 duration-200 ease-linear visible dark:invisible"
        aria-hidden="true"
    ></span>
</x-ui.button>
