@props(['formId'])

<div class="hidden xl:block xl:w-1/5">
  <div class="sticky top-1/2 flex w-full -translate-y-1/2 flex-col">
    {{-- character count --}}
    <div
      class="bg-linear-to-r flex w-full items-center justify-start rounded-xl from-white to-white/0 p-4 dark:from-zinc-700 dark:to-zinc-700/0 dark:text-zinc-50"
      wire:ignore
    >
      <span class="character-counter"></span>
    </div>

    {{-- save button --}}
    <button
      class="focus:outline-hidden focus:ring-3 group mt-4 inline-flex h-14 w-14 cursor-pointer items-center justify-center rounded-xl border border-transparent bg-blue-600 text-zinc-50 ring-blue-300 transition duration-150 ease-in-out focus:border-blue-700 active:bg-blue-700 disabled:bg-slate-600"
      form="{{ $formId }}"
      type="submit"
      wire:loading.attr="disabled"
    >
      <span
        class="text-2xl transition duration-150 ease-in group-hover:rotate-12 group-hover:scale-125"
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
