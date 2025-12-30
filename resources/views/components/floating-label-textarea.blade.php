<div {{ $attributes->filter(fn(string $value, string $key) => $key === 'class')->merge(['class' => 'relative']) }}>
  <textarea
      class="p-4 w-full placeholder-transparent bg-transparent rounded-lg border-2 transition duration-150 ease-in focus:border-emerald-500 peer border-zinc-300 text-zinc-900 dark:focus:border-lividus-500 dark:border-zinc-400 dark:text-zinc-50 focus:outline-hidden"
    {{ $attributes->filter(fn(string $value, string $key) => $key !== 'class') }}
  >{{ $slot }}</textarea>

    <label
        class="absolute -top-3 left-4 px-3 text-base transition-all pointer-events-none bg-zinc-50 text-zinc-600 peer-placeholder-shown:left-7 peer-placeholder-shown:top-4 peer-placeholder-shown:px-0 peer-placeholder-shown:text-lg peer-placeholder-shown:text-zinc-400 peer-focus:-top-3 peer-focus:left-4 peer-focus:px-3 peer-focus:text-base peer-focus:text-emerald-600 dark:peer-focus:text-lividus-500 dark:bg-zinc-800 dark:text-zinc-50 dark:selection:text-zinc-50 dark:peer-placeholder-shown:text-zinc-400"
        for="{{ $attributes->get('id') }}"
    >
        {{ $attributes->get('placeholder') }}
    </label>
</div>
