<div
  {{ $attributes->merge(['class' => 'mt-2 w-48 rounded-md bg-gray-50 p-2 ring-1 ring-black/20 dark:bg-gray-800 dark:text-gray-50 dark:ring-gray-400/40', 'role' => 'menu']) }}
>
  {{ $slot }}
</div>
