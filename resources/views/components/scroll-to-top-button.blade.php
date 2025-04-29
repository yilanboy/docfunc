<button
  {{ $attributes->merge([
      'class' =>
          'dark:bg-lividus-600 fixed bottom-7 right-7 z-10 hidden h-16 w-16 cursor-pointer rounded-full bg-emerald-500 text-zinc-50 transition duration-150 ease-in hover:scale-110',
      'id' => 'scroll-to-top-btn',
      'type' => 'button',
      'title' => 'Go to top',
  ]) }}
>
  <span class="m-auto text-3xl font-bold">
    <x-icons.arrow-up class="w-8" />
  </span>
</button>
