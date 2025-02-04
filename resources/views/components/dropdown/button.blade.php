<button
  {{ $attributes->merge(['class' => 'flex w-full items-center rounded-md px-4 py-2 hover:bg-gray-200 dark:hover:bg-gray-700 cursor-pointer', 'type' => 'button', 'role' => 'menuitem']) }}
>
  {{ $slot }}
</button>
