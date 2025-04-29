<a
  {{ $attributes->merge(['class' => 'flex items-center rounded-md px-4 py-2 hover:bg-zinc-200 dark:hover:bg-zinc-700 cursor-pointer', 'role' => 'menuitem', 'wire:navigate' => '']) }}>
  {{ $slot }}
</a>
