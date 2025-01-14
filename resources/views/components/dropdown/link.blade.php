<a
  {{ $attributes->merge(['class' => 'flex items-center rounded-md px-4 py-2 hover:bg-gray-200 dark:hover:bg-gray-700', 'role' => 'menuitem', 'wire:navigate' => '']) }}>
  {{ $slot }}
</a>
