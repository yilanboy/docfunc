@props(['formId'])

<div class="hidden xl:block xl:w-1/5">
    <div class="flex sticky top-1/2 flex-col w-full -translate-y-1/2">
        {{-- character count --}}
        <div
            class="flex justify-start items-center p-4 w-full from-white rounded-xl bg-linear-to-r to-white/0 dark:from-zinc-700 dark:to-zinc-700/0 dark:text-zinc-50"
            wire:ignore
        >
            <span class="character-counter"></span>
        </div>

        {{-- save button --}}
        <button
            class="inline-flex justify-center items-center mt-4 w-14 h-14 bg-blue-600 rounded-xl border border-transparent ring-blue-300 transition duration-150 ease-in-out cursor-pointer focus:border-blue-700 active:bg-blue-700 group text-zinc-50 focus:outline-hidden focus:ring-3 disabled:bg-slate-600"
            form="{{ $formId }}"
            type="submit"
            wire:loading.attr="disabled"
        >
      <span
          class="text-2xl transition duration-150 ease-in group-hover:scale-125 group-hover:rotate-12"
          wire:loading.remove
      >
        <x-icons.save class="w-6" />
      </span>

            <span
                class="size-10"
                wire:loading
            >
        <x-icons.animate-spin />
      </span>
        </button>
    </div>
</div>
