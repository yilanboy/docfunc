<div
    {{ $attributes->merge(['class' => 'mt-2 w-48 rounded-md bg-zinc-50 p-2 ring-1 ring-black/20 dark:bg-zinc-800 dark:text-zinc-50 dark:ring-zinc-400/40', 'role' => 'menu']) }}
>
    {{ $slot }}
</div>
