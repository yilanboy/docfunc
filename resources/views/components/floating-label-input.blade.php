<div {{ $attributes->filter(fn(string $value, string $key) => $key === 'class')->merge(['class' => 'relative']) }}>
  <input
    class="dark:focus:border-lividus-500 peer h-16 w-full rounded-lg border-2 border-gray-300 bg-transparent p-4 text-gray-900 placeholder-transparent transition duration-150 ease-in focus:border-emerald-500 focus:outline-none dark:border-gray-400 dark:text-gray-50"
    {{ $attributes->filter(fn(string $value, string $key) => $key !== 'class') }}
  >

  <label
    class="dark:peer-focus:text-lividus-500 pointer-events-none absolute -top-3 left-4 bg-gray-50 px-3 text-base text-gray-600 transition-all peer-placeholder-shown:left-7 peer-placeholder-shown:top-4 peer-placeholder-shown:px-0 peer-placeholder-shown:text-lg peer-placeholder-shown:text-gray-400 peer-focus:-top-3 peer-focus:left-4 peer-focus:px-3 peer-focus:text-base peer-focus:text-emerald-600 dark:bg-gray-800 dark:text-gray-50 selection:dark:text-gray-50 dark:peer-placeholder-shown:text-gray-400"
    for="{{ $attributes->get('id') }}"
  >
    {{ $attributes->get('placeholder') }}
  </label>
</div>
