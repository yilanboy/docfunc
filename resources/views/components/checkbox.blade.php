<div class="flex gap-3">
    <div class="flex items-center shrink-0">
        <div class="grid grid-cols-1 group size-5">
            <input
                class="col-start-1 row-start-1 bg-white rounded-sm border appearance-none checked:bg-emerald-600 checked:border-emerald-600 border-zinc-300 indeterminate:border-emerald-600 indeterminate:bg-emerald-600 forced-colors:appearance-auto dark:checked:border-lividus-600 dark:checked:bg-lividus-600 dark:indeterminate:border-lividus-600 dark:indeterminate:bg-lividus-600 dark:focus-visible:outline-lividus-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 disabled:border-zinc-300 disabled:bg-zinc-100 disabled:checked:bg-zinc-100"
                type="checkbox"
                {{ $attributes }}
            >
            <svg
                class="col-start-1 row-start-1 justify-self-center self-center pointer-events-none group-has-disabled:stroke-zinc-950/25 size-3.5 stroke-white"
                viewBox="0 0 14 14"
                fill="none"
            >
                <path
                    class="opacity-0 group-has-checked:opacity-100"
                    d="M3 8L6 11L11 3.5"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                />
                <path
                    class="opacity-0 group-has-indeterminate:opacity-100"
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
            class="font-medium text-zinc-900 dark:text-zinc-50"
            for="{{ $attributes->get('id') }}"
        >{{ $slot }}</label>
    </div>
</div>
