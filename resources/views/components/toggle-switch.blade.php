<label
    class="flex items-center space-x-4 cursor-pointer select-none"
    for="{{ $attributes->get('id') }}"
>
    <div class="flex relative">
        <input
            class="sr-only peer"
            type="checkbox"
            {{ $attributes }}
        />

        {{-- background --}}
        <div
            class="w-14 h-8 rounded-full transition-all duration-300 bg-zinc-500 peer-checked:bg-cyan-400 peer-disabled:bg-zinc-200 dark:peer-disabled:bg-zinc-700"
        >
        </div>

        {{-- dot --}}
        <div
            class="absolute top-1 left-1 w-6 h-6 bg-white rounded-full transition-all duration-300 peer-checked:left-7 peer-disabled:bg-zinc-100 dark:peer-disabled:bg-zinc-600"
        >
        </div>

        <span class="ml-4 peer-disabled:text-zinc-400 dark:text-zinc-50 dark:peer-disabled:text-zinc-600">
      {{ $slot }}
    </span>
    </div>
</label>
