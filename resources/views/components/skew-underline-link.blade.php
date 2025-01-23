{{-- prettier-ignore-start --}}
@props([
    'link',
    'icon' => '',
    'selected' => false
])
{{-- prettier-ignore-end --}}

<a
  class="outline-hidden active:outline-hidden group relative inline-block h-auto w-auto cursor-pointer items-center justify-center rounded-sm px-1 text-lg text-gray-900/80 transition-all duration-300 hover:-rotate-3 dark:text-gray-50"
  href="{{ $link }}"
  {{ $attributes }}
  wire:navigate
>
  <span class="relative z-20 flex items-center justify-center">
    @if (empty($icon))
      <x-icon.home class="w-5" />
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
