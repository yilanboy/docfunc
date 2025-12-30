<div>
    <label
        class="hidden"
        for="{{ $attributes->get('id') }}"
    >{{ $label }}</label>

    <div class="grid grid-cols-1">
        <select
            class="col-start-1 row-start-1 py-2 pr-8 pl-4 w-full h-12 text-lg bg-white rounded-md appearance-none text-zinc-900 outline-1 -outline-offset-1 outline-zinc-300 dark:focus:outline-lividus-500 dark:bg-zinc-700 dark:text-zinc-50 dark:outline-zinc-600 focus:outline-2 focus:-outline-offset-2 focus:outline-emerald-500"
            {{ $attributes }}
        >
            {{ $slot }}
        </select>
        <svg
            class="col-start-1 row-start-1 justify-self-end self-center mr-2 pointer-events-none size-5 text-zinc-500 sm:size-4 dark:text-zinc-50"
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
