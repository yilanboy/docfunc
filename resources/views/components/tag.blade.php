@props(['href'])

<a
    class="inline-flex items-center py-1 px-2 m-1 text-xs font-medium text-emerald-700 bg-emerald-100 rounded-md hover:bg-emerald-200 dark:bg-lividus-700 dark:hover:bg-lividus-600 dark:text-zinc-50"
    href="{{ $href }}"
    wire:navigate
>
    {{ $slot }}
</a>
