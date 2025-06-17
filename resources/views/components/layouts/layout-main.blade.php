<div
  class="relative selection:bg-emerald-300 selection:text-emerald-900 dark:selection:bg-indigo-300 dark:selection:text-indigo-900"
  {{ $attributes }}
>
  <x-background />

  <div class="flex min-h-screen flex-col">
    <livewire:shared.header-part />

    {{ $slot }}
  </div>

  <x-layouts.footer />
</div>
