<div>
  <label
    class="hidden"
    for="{{ $attributes->get('id') }}"
  >{{ $label }}</label>

  <div class="grid grid-cols-1">
    <select
      class="dark:focus:outline-lividus-500 col-start-1 row-start-1 h-12 w-full appearance-none rounded-md bg-white py-2 pl-4 pr-8 text-lg text-zinc-900 outline-1 -outline-offset-1 outline-zinc-300 focus:outline-2 focus:-outline-offset-2 focus:outline-emerald-500 dark:bg-zinc-700 dark:text-zinc-50 dark:outline-zinc-600"
      {{ $attributes }}
    >
      {{ $slot }}
    </select>
    <svg
      class="pointer-events-none col-start-1 row-start-1 mr-2 size-5 self-center justify-self-end text-zinc-500 sm:size-4 dark:text-zinc-50"
      data-slot="icon"
      aria-hidden="true"
      viewBox="0 0 16 16"
      fill="currentColor"
    >
      <path
        fill-rule="evenodd"
        d="M4.22 6.22a.75.75 0 0 1 1.06 0L8 8.94l2.72-2.72a.75.75 0 1 1 1.06 1.06l-3.25 3.25a.75.75 0 0 1-1.06 0L4.22 7.28a.75.75 0 0 1 0-1.06Z"
        clip-rule="evenodd"
      />
    </svg>
  </div>
</div>
