@props(['formId'])

<div class="hidden xl:block xl:w-1/5">
  <div class="sticky top-1/2 flex w-full -translate-y-1/2 flex-col">
    {{-- character count --}}
    <div
      class="flex w-full items-center justify-start rounded-xl bg-linear-to-r from-white to-white/0 p-4 dark:from-gray-700 dark:to-gray-700/0 dark:text-gray-50"
      wire:ignore
    >
      <span class="character-counter"></span>
    </div>

    {{-- save button --}}
    <button
      class="group mt-4 inline-flex h-14 w-14 items-center justify-center rounded-xl border border-transparent bg-blue-600 text-gray-50 ring-blue-300 transition duration-150 ease-in-out focus:border-blue-700 focus:outline-hidden focus:ring-3 active:bg-blue-700 disabled:bg-slate-600"
      form="{{ $formId }}"
      type="submit"
      wire:loading.attr="disabled"
    >
      <span
        class="text-2xl transition duration-150 ease-in group-hover:rotate-12 group-hover:scale-125"
        wire:loading.remove
      >
        <x-icon.save class="w-6" />
      </span>

      <span
        class="size-10"
        wire:loading
      >
        <x-icon.animate-spin />
      </span>
    </button>
  </div>
</div>
