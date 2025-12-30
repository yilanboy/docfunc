{{-- prettier-ignore-start --}}
@props([
    'link',
    'icon' => '',
    'selected' => false
])
{{-- prettier-ignore-end --}}

<a
    class="inline-block relative justify-center items-center px-1 w-auto h-auto text-lg rounded-sm transition-all duration-300 cursor-pointer hover:-rotate-3 outline-hidden group text-zinc-900/80 dark:text-zinc-50 active:outline-hidden"
    href="{{ $link }}"
    {{ $attributes }}
    wire:navigate
>
  <span class="flex relative z-20 justify-center items-center">
    @if (empty($icon))
          <x-icons.home class="w-5" />
      @else
          <div class="w-5">{!! $icon !!}</div>
      @endif

    <span class="ml-2">{{ $slot }}</span>
  </span>
    <span @class([
      'absolute bottom-0 left-0 z-10 h-2 w-0 skew-x-12 bg-emerald-400 dark:bg-lividus-600',
      'transition-all duration-300 ease-out group-hover:w-full' => !$selected,
      'w-full' => $selected,
  ])></span>
</a>
