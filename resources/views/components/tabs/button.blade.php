<button
  type="button"
  {{ $attributes->merge([
      'class' =>
          'relative z-20 inline-flex cursor-pointer items-center justify-center gap-2 whitespace-nowrap rounded-md px-3 py-1.5 text-sm font-medium',
  ]) }}
>
  {{ $slot }}
</button>
