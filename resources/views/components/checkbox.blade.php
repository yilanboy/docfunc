<div class="flex gap-3">
  <div class="flex shrink-0 items-center">
    <div class="group grid size-5 grid-cols-1">
      <input
        class="dark:checked:border-lividus-600 dark:checked:bg-lividus-600 dark:indeterminate:border-lividus-600 dark:indeterminate:bg-lividus-600 dark:focus-visible:outline-lividus-600 col-start-1 row-start-1 appearance-none rounded-sm border border-gray-300 bg-white checked:border-emerald-600 checked:bg-emerald-600 indeterminate:border-emerald-600 indeterminate:bg-emerald-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 disabled:border-gray-300 disabled:bg-gray-100 disabled:checked:bg-gray-100 forced-colors:appearance-auto"
        type="checkbox"
        {{ $attributes }}
      >
      <svg
        class="group-has-disabled:stroke-gray-950/25 pointer-events-none col-start-1 row-start-1 size-3.5 self-center justify-self-center stroke-white"
        viewBox="0 0 14 14"
        fill="none"
      >
        <path
          class="group-has-checked:opacity-100 opacity-0"
          d="M3 8L6 11L11 3.5"
          stroke-width="2"
          stroke-linecap="round"
          stroke-linejoin="round"
        />
        <path
          class="group-has-indeterminate:opacity-100 opacity-0"
          d="M3 7H11"
          stroke-width="2"
          stroke-linecap="round"
          stroke-linejoin="round"
        />
      </svg>
    </div>
  </div>
  <div class="text-base">
    <label
      class="font-medium text-gray-900 dark:text-gray-50"
      for="{{ $attributes->get('id') }}"
    >{{ $slot }}</label>
  </div>
</div>
