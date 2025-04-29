<button
  {{ $attributes->merge(['class' => 'flex w-full items-center rounded-md px-4 py-2 hover:bg-zinc-200 dark:hover:bg-zinc-700 cursor-pointer', 'type' => 'button', 'role' => 'menuitem']) }}
>
  {{ $slot }}
</button>
