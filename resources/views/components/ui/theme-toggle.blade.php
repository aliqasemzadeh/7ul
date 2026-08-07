<x-ui.button
    variant="ghost"
    size="sm"
    :iconOnly="true"
    x-on:click="$store.theme.toggle()"
    :aria-label="__('app.auth.toggle_theme')"
    class="relative shrink-0 overflow-hidden"
>
    <span
        class="iconify icon-[hugeicons--moon-02] size-4 transition duration-200 ease-linear dark:scale-0 dark:opacity-0"
        aria-hidden="true"
    ></span>
    <span
        class="iconify icon-[hugeicons--sun-01] absolute inset-0 m-auto size-4 transition duration-200 ease-linear scale-0 opacity-0 dark:scale-100 dark:opacity-100"
        aria-hidden="true"
    ></span>
</x-ui.button>
