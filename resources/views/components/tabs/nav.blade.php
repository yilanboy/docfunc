@props(['tabInit'])

@script
  <script>
    Alpine.data('tabs', () => ({
      tabSelected: @js($tabInit),
      tabButtonClicked(tabButton) {
        this.tabSelected = tabButton.id.replace('-tab-button', '');
        this.tabRepositionMarker(tabButton);
      },
      tabRepositionMarker(tabButton) {
        this.$refs.tabMarker.style.width = tabButton.offsetWidth + 'px';
        this.$refs.tabMarker.style.height = tabButton.offsetHeight + 'px';
        this.$refs.tabMarker.style.left = tabButton.offsetLeft + 'px';
      },
      init() {
        let tabSelectedButtons = document.getElementById(this.tabSelected + '-tab-button');
        this.tabRepositionMarker(tabSelectedButtons);
      }
    }));
  </script>
@endscript

<nav
  x-data="tabs"
  x-modelable="tabSelected"
  {{ $attributes->merge([
      'class' =>
          'relative z-0 inline-grid w-full select-none grid-cols-3 items-center justify-center gap-1 rounded-xl bg-zinc-300/50 p-1 text-zinc-500 dark:bg-zinc-500/30 dark:text-zinc-50',
  ]) }}
>
  {{ $slot }}

  <div
    class="absolute left-0 z-10 h-full w-fit duration-300 ease-out"
    x-ref="tabMarker"
    x-cloak
  >
    <div class="size-full rounded-[calc(var(--radius-xl)-(--spacing(1)))] bg-zinc-50 dark:bg-zinc-800"></div>
  </div>
</nav>
