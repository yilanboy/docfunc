@props(['href'])

<a
  class="dark:bg-lividus-700 dark:hover:bg-lividus-600 m-1 inline-flex items-center rounded-md bg-emerald-100 px-2 py-1 text-xs font-medium text-emerald-700 hover:bg-emerald-200 dark:text-gray-50"
  href="{{ $href }}"
  wire:navigate
>
  {{ $slot }}
</a>
