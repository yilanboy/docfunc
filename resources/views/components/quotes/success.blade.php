<div
  {{ $attributes->merge(['class' => 'relative ml-4 rounded-md border-none bg-emerald-300/20 px-4 py-2 text-emerald-500 before:absolute before:-left-4 before:top-0 before:h-full before:w-1.5 before:rounded-sm before:bg-emerald-500 before:contain-none dark:text-emerald-400 dark:before:bg-emerald-400']) }}
>
  {{ $slot }}
</div>
