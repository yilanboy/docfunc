<label
  class="flex cursor-pointer select-none items-center space-x-4"
  for="{{ $attributes->get('id') }}"
>
  <div class="relative flex">
    <input
      class="peer sr-only"
      type="checkbox"
      {{ $attributes }}
    />

    {{-- background --}}
    <div
      class="h-8 w-14 rounded-full bg-gray-500 transition-all duration-300 peer-checked:bg-cyan-400 peer-disabled:bg-gray-200 dark:peer-disabled:bg-gray-700"
    >
    </div>

    {{-- dot --}}
    <div
      class="absolute left-1 top-1 h-6 w-6 rounded-full bg-white transition-all duration-300 peer-checked:left-7 peer-disabled:bg-gray-100 dark:peer-disabled:bg-gray-600"
    >
    </div>

    <span class="ml-4 peer-disabled:text-gray-400 dark:text-gray-50 dark:peer-disabled:text-gray-600">
      {{ $slot }}
    </span>
  </div>
</label>
