<nav
    {{ $attributes->merge([
        'class' =>
            'relative z-0 inline-grid w-full select-none grid-cols-3 items-center justify-center gap-1 rounded-xl bg-zinc-300/50 p-1 text-zinc-500 dark:bg-zinc-500/30 dark:text-zinc-50',
    ]) }}
>
    {{ $slot }}
</nav>
